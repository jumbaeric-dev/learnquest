<?php

namespace App\Services\Billing\Gateways;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Exceptions\Billing\PaymentGatewayUnavailableException;
use App\Exceptions\Billing\WebhookAuthenticationException;
use App\Models\Payment;
use App\Services\Billing\DTO\CheckoutResult;
use App\Services\Billing\DTO\PaymentVerificationResult;
use App\Services\Billing\DTO\VerifiedPaymentWebhook;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use JsonException;
use Illuminate\Support\Facades\Log;

/** Paystack REST adapter. Test/live mode is determined by the configured secret key. */
final class PaystackGateway extends AbstractUnavailablePaymentGateway
{
    private const API_BASE_URL = 'https://api.paystack.co';

    public function initiateCheckout(Payment $payment): CheckoutResult
    {
        $this->assertReady($payment);

        $parent = $payment->user;
        if (! $parent || ! $parent->isParent() || ! filter_var($parent->email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'payment' => 'A valid parent account with an email address is required.',
            ]);
        }

        try {
            $response = $this->request()->post(self::API_BASE_URL . '/transaction/initialize', [
                'email' => $parent->email,
                'amount' => (string) $payment->amount_minor,
                'currency' => $payment->currency,
                'reference' => $payment->reference,
                'callback_url' => route('parent.billing', absolute: true),
            ]);
        } catch (ConnectionException $exception) {
            throw new PaymentGatewayException('Paystack checkout initialization could not be reached.', previous: $exception);
        }

        $data = $this->successfulJson($response->status(), $response->json());
        $authorizationUrl = data_get($data, 'data.authorization_url');
        $accessCode = data_get($data, 'data.access_code');
        $reference = data_get($data, 'data.reference');

        if (
            ! is_string($authorizationUrl)
            || ! filter_var($authorizationUrl, FILTER_VALIDATE_URL)
            || parse_url($authorizationUrl, PHP_URL_SCHEME) !== 'https'
            || ! is_string($reference)
            || ! hash_equals($payment->reference, $reference)
        ) {
            throw new PaymentGatewayException('Paystack returned an incomplete or mismatched checkout response.');
        }

        $metadata = is_array($payment->metadata) ? $payment->metadata : [];
        $metadata['paystack_transaction_reference'] = $reference;
        $metadata['paystack_checkout_url'] = $authorizationUrl;
        if (is_string($accessCode) && $accessCode !== '') {
            $metadata['paystack_access_code'] = $accessCode;
        }
        $payment->forceFill(['metadata' => $metadata])->save();

        return new CheckoutResult(
            checkoutUrl: $authorizationUrl,
            providerReference: $reference,
            expiresAt: $payment->expires_at?->toDateTimeImmutable(),
            metadata: ['provider' => 'paystack', 'flow' => 'hosted_checkout'],
        );
    }

    public function verifyPayment(Payment $payment): PaymentVerificationResult
    {
        $this->assertReady($payment);
        $reference = data_get($payment->metadata, 'paystack_transaction_reference', $payment->reference);
        if (! is_string($reference) || $reference === '' || ! hash_equals($payment->reference, $reference)) {
            throw new PaymentGatewayException('The Paystack transaction reference does not match this payment.');
        }

        try {
            $response = $this->request()->get(self::API_BASE_URL . '/transaction/verify/' . rawurlencode($reference));
        } catch (ConnectionException $exception) {
            throw new PaymentGatewayException('Paystack transaction verification could not be reached.', previous: $exception);
        }
        $data = $this->successfulJson($response->status(), $response->json());
        $transaction = data_get($data, 'data');

        $transactionId = is_array($transaction) ? data_get($transaction, 'id') : null;
        if (
            ! is_array($transaction)
            || ! is_string(data_get($transaction, 'reference'))
            || ! hash_equals($payment->reference, (string) data_get($transaction, 'reference'))
            || (! is_int($transactionId) && !(is_string($transactionId) && ctype_digit($transactionId)))
        ) {
            throw new PaymentGatewayException('Paystack verification response does not match this payment.');
        }

        $amount = data_get($transaction, 'amount');
        if (! is_int($amount) && !(is_string($amount) && ctype_digit($amount))) {
            throw new PaymentGatewayException('Paystack returned an invalid payment amount.');
        }

        $currency = strtoupper((string) data_get($transaction, 'currency'));
        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new PaymentGatewayException('Paystack returned an invalid transaction currency.');
        }
        $state = strtolower((string) data_get($transaction, 'status'));
        $outcome = match ($state) {
            'success' => 'succeeded',
            'failed', 'reversed' => 'failed',
            'pending', 'ongoing', 'processing', 'queued', 'abandoned' => 'pending',
            default => throw new PaymentGatewayException('Paystack returned an unsupported transaction state.'),
        };

        return new PaymentVerificationResult(
            outcome: $outcome,
            providerTransactionId: (string) $transactionId,
            amountMinor: (int) $amount,
            currency: $currency,
            providerState: strtoupper($state),
        );
    }

    public function parseWebhook(Request $request): VerifiedPaymentWebhook
    {
        $rawBody = $request->getContent();
        $this->assertWebhookSignature($request, $rawBody);

        try {
            $payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new PaymentGatewayException('Paystack webhook payload is not valid JSON.');
        }

        if (! is_array($payload) || ($payload['event'] ?? null) !== 'charge.success') {
            throw new PaymentGatewayException('Unsupported Paystack webhook event.');
        }

        $transaction = $payload['data'] ?? null;
        $reference = is_array($transaction) ? ($transaction['reference'] ?? null) : null;
        $amount = is_array($transaction) ? ($transaction['amount'] ?? null) : null;
        $currency = is_array($transaction) ? strtoupper((string) ($transaction['currency'] ?? '')) : '';
        $transactionId = is_array($transaction) ? ($transaction['id'] ?? null) : null;

        if (
            ! is_string($reference) || $reference === ''
            || (! is_int($amount) && !(is_string($amount) && ctype_digit($amount)))
            || ! preg_match('/^[A-Z]{3}$/', $currency)
        ) {
            throw new PaymentGatewayException('Paystack webhook is missing valid transaction details.');
        }

        $authorization = $transaction['authorization'] ?? null;
        $paymentMethod = is_array($authorization) && is_string($authorization['channel'] ?? null)
            ? $authorization['channel'] : null;

        return new VerifiedPaymentWebhook(
            provider: 'paystack',
            eventId: 'charge.success:' . $reference . ':' . (string) ($transactionId ?? 'unknown'),
            eventType: 'charge.success',
            paymentReference: $reference,
            providerTransactionId: $transactionId === null ? null : (string) $transactionId,
            amountMinor: (int) $amount,
            currency: $currency,
            outcome: 'succeeded',
            signatureVerifiedAt: now()->toDateTimeImmutable(),
            metadata: [
                'channel' => is_string($transaction['channel'] ?? null) ? $transaction['channel'] : 'paystack',
                'payment_method' => $paymentMethod,
                'provider_status' => 'success',
            ],
            providerResourceId: (string) ($transactionId ?? $reference),
        );
    }

    private function assertWebhookSignature(Request $request, string $rawBody): void
    {
        $modernHeader = $request->header('Paystack-Signature');
        if (is_string($modernHeader) && $modernHeader !== '') {
            $secret = $this->setting('webhook_secret');
            if (! is_string($secret) || $secret === '') {
                throw new PaymentGatewayUnavailableException('Paystack webhook signing secret is not configured.');
            }

            $timestamp = null;
            $signatures = [];
            foreach (explode(',', $modernHeader) as $part) {
                [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
                if ($key === 't' && is_string($value) && ctype_digit($value)) {
                    $timestamp = (int) $value;
                } elseif ($key === 'v1' && is_string($value) && str_contains($value, ':')) {
                    [, $signature] = explode(':', $value, 2);
                    if (preg_match('/^[a-f0-9]{128}$/i', $signature)) {
                        $signatures[] = strtolower($signature);
                    }
                }
            }

            if ($timestamp === null || abs(time() - $timestamp) > 300 || $signatures === []) {
                throw new WebhookAuthenticationException('Paystack webhook signature is invalid or expired.');
            }

            $expected = hash_hmac('sha512', $timestamp . '.' . $rawBody, $secret);
            foreach ($signatures as $signature) {
                if (hash_equals($expected, $signature)) {
                    return;
                }
            }

            throw new WebhookAuthenticationException('Paystack webhook signature verification failed.');
        }

        // Legacy Paystack-Signature mode: HMAC SHA512 of the raw body using the secret API key.
        $legacySignature = (string) $request->header('x-paystack-signature', '');
        $secretKey = $this->setting('secret_key');
        if (! is_string($secretKey) || $secretKey === '') {
            throw new PaymentGatewayUnavailableException('Paystack API credentials are not configured.');
        }
        if (
            ! preg_match('/^[a-f0-9]{128}$/i', $legacySignature)
            || ! hash_equals(hash_hmac('sha512', $rawBody, $secretKey), strtolower($legacySignature))
        ) {
            throw new WebhookAuthenticationException('Paystack webhook signature verification failed.');
        }
    }

    private function assertReady(Payment $payment): void
    {
        if (
            $payment->provider !== 'paystack'
            || ! in_array($payment->currency, (array) $this->setting('currencies'), true)
        ) {
            throw new PaymentGatewayException('The payment is not supported by this Paystack configuration.');
        }
        $secret = $this->setting('secret_key');
        if (! is_string($secret) || $secret === '') {
            throw new PaymentGatewayUnavailableException('Paystack secret key is not configured.');
        }
        if (! str_starts_with($secret, 'sk_test_') && ! str_starts_with($secret, 'sk_live_')) {
            throw new PaymentGatewayUnavailableException('The configured Paystack secret key has an invalid format.');
        }
    }

    private function request()
    {
        return Http::acceptJson()
            ->asJson()
            ->withToken($this->setting('secret_key'))
            ->connectTimeout(5)
            ->timeout(20);
    }

    private function successfulJson(int $status, mixed $data): array
    {
        if (
            $status < 200
            || $status >= 300
            || ! is_array($data)
            || ($data['status'] ?? false) !== true
        ) {
            Log::warning('Paystack API rejected request', [
                'http_status' => $status,
                'response' => is_array($data) ? $data : null,
            ]);

            throw new PaymentGatewayException(
                'Paystack request failed or returned an invalid response.'
            );
        }

        return $data;
    }

    private function setting(string $key): mixed
    {
        return config("learnquest.gateways.providers.paystack.{$key}");
    }
}
