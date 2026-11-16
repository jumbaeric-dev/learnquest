<?php

namespace App\Services\Billing\Gateways;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Exceptions\Billing\PaymentGatewayUnavailableException;
use App\Models\Payment;
use App\Services\Billing\DTO\CheckoutResult;
use App\Services\Billing\DTO\PaymentVerificationResult;
use App\Services\Billing\DTO\VerifiedPaymentWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Billing\WebhookAuthenticationException;

/** IntaSend REST adapter. Test/live mode is selected by the API key prefix. */
final class IntaSendGateway extends AbstractUnavailablePaymentGateway
{
    private const API_VERSION = '/api/v1';

    public function initiateCheckout(Payment $payment): CheckoutResult
    {
        $this->assertReady($payment, requirePublicKey: true);

        $user = $payment->user;
        if (!$user || !$user->isParent()) {
            throw ValidationException::withMessages([
                'payment' => 'A valid parent account is required.',
            ]);
        }

        $response = $this->publicRequest()
            ->post($this->endpoint('/checkout/'), [
                'amount' => round($payment->amount_minor / 100, 2),
                'currency' => $payment->currency,
                'email' => $user->email,
                'first_name' => Str::before(trim((string) $user->name), ' '),
                'last_name' => Str::after(trim((string) $user->name), ' ') ?: 'Parent',
                'api_ref' => $payment->reference,
                'unique_api_ref' => true,
                'redirect_url' => route('parent.billing', absolute: true),
                'host' => rtrim((string) config('app.url'), '/'),
                'channel' => 'WEBSITE',
                'card_tarrif' => 'BUSINESS-PAYS',
                'mobile_tarrif' => 'BUSINESS-PAYS',
            ]);

        $data = $this->successfulJson(
            $response->status(),
            $response->json(),
            $response->body(),
            $response->header('Content-Type'),
        );

        $invoiceId = data_get($data, 'invoice.invoice_id')
            ?? data_get($data, 'invoice_id')
            ?? data_get($data, 'id');
        $url = data_get($data, 'url');

        if (!is_string($invoiceId) || $invoiceId === '' || !is_string($url)) {
            throw new PaymentGatewayException('IntaSend returned an incomplete checkout response.');
        }

        $this->storeInvoiceReference($payment, $invoiceId);

        return new CheckoutResult(
            checkoutUrl: $url,
            providerReference: $invoiceId,
            expiresAt: $payment->expires_at?->toDateTimeImmutable(),
            metadata: ['provider' => 'intasend', 'flow' => 'hosted_checkout'],
        );
    }

    /** Initiate a direct M-Pesa STK Push. Phone must be supplied by a trusted parent-facing flow. */
    public function initiateMpesaStkPush(Payment $payment, string $phoneNumber): CheckoutResult
    {
        if ($payment->currency !== 'KES' || $payment->amount_minor % 100 !== 0) {
            throw ValidationException::withMessages(['payment' => 'M-Pesa STK Push only supports KES payments.']);
        }

        $phone = preg_replace('/[\s()+-]/', '', trim($phoneNumber));
        if (!is_string($phone) || !preg_match('/^(?:254[17]\d{8}|0[17]\d{8})$/', $phone)) {
            throw ValidationException::withMessages(['phone_number' => 'Enter a valid Kenyan mobile number.']);
        }
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }

        $this->assertReady($payment);
        $user = $payment->user;
        if (!$user || !$user->isParent()) {
            throw ValidationException::withMessages(['payment' => 'A valid parent account is required.']);
        }

        $response = $this->request()->post($this->endpoint('/payment/mpesa-stk-push/'), [
            'amount' => (int) ceil($payment->amount_minor / 100),
            'phone_number' => $phone,
            'api_ref' => $payment->reference,
        ]);

        $data = $this->successfulJson($response->status(), $response->json());
        $invoiceId = data_get($data, 'invoice.invoice_id') ?? data_get($data, 'invoice_id');
        if (!is_string($invoiceId) || $invoiceId === '') {
            throw new PaymentGatewayException('IntaSend did not return an M-Pesa invoice reference.');
        }

        $this->storeInvoiceReference($payment, $invoiceId);

        return new CheckoutResult(
            checkoutUrl: null,
            providerReference: $invoiceId,
            expiresAt: $payment->expires_at?->toDateTimeImmutable(),
            metadata: ['provider' => 'intasend', 'flow' => 'mpesa_stk_push'],
            interaction: 'push',
        );
    }

    public function verifyPayment(Payment $payment): PaymentVerificationResult
    {
        $this->assertReady($payment);
        $invoiceId = data_get($payment->metadata, 'intasend_invoice_id');
        if (!is_string($invoiceId) || $invoiceId === '') {
            throw new PaymentGatewayException('The IntaSend invoice reference is missing.');
        }

        $response = $this->request()->post($this->endpoint('/payment/status/'), ['invoice_id' => $invoiceId]);
        $data = $this->successfulJson($response->status(), $response->json());
        $invoice = data_get($data, 'invoice', $data);

        if (
            (string) data_get($invoice, 'invoice_id') !== $invoiceId
            || (string) data_get($invoice, 'api_ref') !== $payment->reference
        ) {
            throw new PaymentGatewayException('IntaSend status response does not match this payment.');
        }

        $amountMinor = $this->minorAmount(data_get($invoice, 'value'));
        $currency = strtoupper((string) data_get($invoice, 'currency'));
        $state = strtoupper((string) data_get($invoice, 'state'));
        $outcome = match ($state) {
            'COMPLETE' => 'succeeded',
            'FAILED', 'CANCELED' => 'failed',
            'PENDING', 'PROCESSING', 'RETRY', 'PARTIAL' => 'pending',
            default => throw new PaymentGatewayException('IntaSend returned an unsupported invoice state.'),
        };

        return new PaymentVerificationResult(
            $outcome,
            data_get($invoice, 'provider_ref') ?: data_get($invoice, 'mpesa_reference'),
            $amountMinor,
            $currency,
            $state,
        );
    }

    public function parseWebhook(Request $request): VerifiedPaymentWebhook
    {
        $secret = $this->setting('webhook_secret');
        if (!is_string($secret) || $secret === '') {
            throw new PaymentGatewayUnavailableException('IntaSend webhook challenge is not configured.');
        }

        $payload = $request->json()->all();
        $challenge = (string) ($payload['challenge'] ?? '');
        if ($challenge === '' || !hash_equals($secret, $challenge)) {
            throw new WebhookAuthenticationException('IntaSend webhook challenge verification failed.');
        }

        // Collection events are the only ones handled. Some payloads omit "topic".
        if (isset($payload['topic']) && $payload['topic'] !== 'collection_event') {
            throw new PaymentGatewayException('Unsupported IntaSend webhook topic.');
        }

        $invoiceId = $payload['invoice_id'] ?? null;
        $reference = $payload['api_ref'] ?? null;
        $state = strtoupper((string) ($payload['state'] ?? ''));
        if (!is_string($invoiceId) || $invoiceId === '' || !is_string($reference) || $reference === '') {
            throw new PaymentGatewayException('IntaSend webhook is missing payment identifiers.');
        }

        $outcome = match ($state) {
            'COMPLETE' => 'succeeded',
            'FAILED', 'CANCELED' => 'failed',
            'PENDING', 'PROCESSING', 'RETRY', 'PARTIAL' => 'pending',
            default => throw new PaymentGatewayException('Unsupported IntaSend invoice state.'),
        };
        $eventId = $invoiceId . ':' . $state;

        return new VerifiedPaymentWebhook(
            provider: 'intasend',
            eventId: $eventId,
            eventType: 'collection_event',
            paymentReference: $reference,
            providerTransactionId: $payload['provider_ref'] ?? $payload['mpesa_reference'] ?? null,
            amountMinor: $this->minorAmount($payload['value'] ?? null),
            currency: strtoupper((string) ($payload['currency'] ?? '')),
            outcome: $outcome,
            signatureVerifiedAt: now()->toDateTimeImmutable(),
            metadata: [
                'channel' => 'intasend',
                'payment_method' => is_string($payload['provider'] ?? null) ? $payload['provider'] : null,
                'provider_status' => $state,
            ],
            providerResourceId: $invoiceId,
        );
    }

    private function assertReady(Payment $payment, bool $requirePublicKey = false): void
    {
        if ($payment->provider !== 'intasend' || !in_array($payment->currency, ['KES', 'USD', 'EUR', 'GBP'], true)) {
            throw new PaymentGatewayException('The payment is not supported by this IntaSend configuration.');
        }
        foreach (['secret_key'] as $key) {
            if (!is_string($this->setting($key)) || $this->setting($key) === '') {
                throw new PaymentGatewayUnavailableException('IntaSend credentials are not configured.');
            }
        }
        if ($requirePublicKey && (!is_string($this->setting('public_key')) || $this->setting('public_key') === '')) {
            throw new PaymentGatewayUnavailableException('IntaSend publishable key is not configured.');
        }
    }

    private function request()
    {
        return Http::acceptJson()
            ->asJson()
            ->withToken($this->setting('secret_key'))
            ->connectTimeout(5)
            ->timeout(15);
    }

    private function publicRequest()
    {
        return Http::acceptJson()
            ->asJson()
            ->withHeaders(['X-IntaSend-Public-API-Key' => $this->setting('public_key')])
            ->connectTimeout(5)
            ->timeout(15);
    }

    private function endpoint(string $path): string
    {
        $base = rtrim((string) $this->setting('base_url'), '/');
        if (!str_starts_with($base, 'https://')) {
            throw new PaymentGatewayUnavailableException('IntaSend API base URL must use HTTPS.');
        }
        return $base . self::API_VERSION . $path;
    }

    private function setting(string $key): mixed
    {
        return config("learnquest.gateways.providers.intasend.{$key}");
    }

    private function successfulJson(
        int $status,
        mixed $data,
        ?string $rawBody = null,
        ?string $contentType = null,
    ): array {
        if ($status < 200 || $status >= 300 || ! is_array($data)) {
            $excerpt = strip_tags((string) $rawBody);

            // Avoid putting customer contact details into application logs.
            $excerpt = preg_replace(
                '/[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}/',
                '[redacted-email]',
                $excerpt,
            );

            $excerpt = preg_replace(
                '/\b(?:\+?254|0)[17]\d{8}\b/',
                '[redacted-phone]',
                $excerpt,
            );

            Log::warning('IntaSend API returned an unsuccessful response.', [
                'status' => $status,
                'content_type' => $contentType,
                'response_excerpt' => mb_substr($excerpt, 0, 1200),
            ]);

            throw new PaymentGatewayException(
                'IntaSend request failed or returned an invalid response.'
            );
        }

        return $data;
    }

    private function storeInvoiceReference(Payment $payment, string $invoiceId): void
    {
        $metadata = is_array($payment->metadata) ? $payment->metadata : [];
        $metadata['intasend_invoice_id'] = $invoiceId;
        $payment->forceFill(['metadata' => $metadata])->save();
    }

    private function majorAmount(int $minor): string
    {
        return intdiv($minor, 100) . '.' . str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    private function minorAmount(mixed $major): int
    {
        if (!is_string($major) && !is_int($major) && !is_float($major)) {
            throw new PaymentGatewayException('IntaSend returned an invalid payment amount.');
        }
        if (is_float($major)) {
            if (!is_finite($major) || $major < 0) {
                throw new PaymentGatewayException('IntaSend returned an invalid payment amount.');
            }
            $major = number_format($major, 2, '.', '');
        }
        $value = trim((string) $major);
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            throw new PaymentGatewayException('IntaSend returned an invalid payment amount.');
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}
