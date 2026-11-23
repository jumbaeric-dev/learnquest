<?php

namespace App\Services\Billing\Gateways;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Exceptions\Billing\PaymentGatewayUnavailableException;
use App\Models\Payment;
use App\Services\Billing\DTO\CheckoutResult;
use App\Services\Billing\DTO\PaymentVerificationResult;
use App\Services\Billing\DTO\VerifiedPaymentWebhook;
use App\Exceptions\Billing\WebhookAuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** PayPal REST adapter for hosted Orders v2 checkout and server-side capture. */
final class PayPalGateway extends AbstractUnavailablePaymentGateway
{
    private const ALLOWED_BASE_URLS = [
        'https://api-m.sandbox.paypal.com',
        'https://api-m.paypal.com',
    ];

    public function initiateCheckout(Payment $payment): CheckoutResult
    {
        $this->assertCredentialsAndCurrency($payment);

        try {
            $accessToken = $this->accessToken();
            $response = Http::acceptJson()
                ->asJson()
                ->withToken($accessToken)
                ->withHeaders([
                    'PayPal-Request-Id' => $this->requestId('order', $payment),
                    'Prefer' => 'return=representation',
                ])
                ->connectTimeout(5)
                ->timeout(20)
                ->post($this->baseUrl() . '/v2/checkout/orders', [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $payment->reference,
                        'custom_id' => $payment->reference,
                        'invoice_id' => $payment->reference,
                        'description' => sprintf('LearnQuest %s plan (%d month%s)', $payment->plan_key, $payment->duration_months, $payment->duration_months === 1 ? '' : 's'),
                        'amount' => [
                            'currency_code' => $payment->currency,
                            'value' => $this->formatAmount($payment->amount_minor),
                        ],
                    ]],
                    'application_context' => [
                        'brand_name' => 'LearnQuest',
                        'user_action' => 'PAY_NOW',
                        'return_url' => route('parent.billing', ['paypal' => 'return'], absolute: true),
                        'cancel_url' => route('parent.billing', ['paypal' => 'cancel'], absolute: true),
                        'shipping_preference' => 'NO_SHIPPING',
                    ],
                ]);
        } catch (ConnectionException $exception) {
            throw new PaymentGatewayUnavailableException('PayPal checkout initialization could not be reached.', previous: $exception);
        }

        $data = $this->successfulJson($response->status(), $response->json(), 'PayPal order creation');
        $orderId = data_get($data, 'id');
        $approvalLink = collect(data_get($data, 'links', []))
            ->first(fn($link) => is_array($link) && in_array($link['rel'] ?? null, ['approve', 'payer-action'], true));
        $approvalUrl = is_array($approvalLink) ? ($approvalLink['href'] ?? null) : null;

        if (
            ! is_string($orderId) || ! preg_match('/^[A-Z0-9]{1,128}$/', $orderId)
            || ! $this->isTrustedApprovalUrl($approvalUrl)
        ) {
            throw new PaymentGatewayException('PayPal returned an incomplete or invalid order approval response.');
        }

        $metadata = is_array($payment->metadata) ? $payment->metadata : [];
        $metadata['paypal_order_id'] = $orderId;
        $metadata['paypal_checkout_url'] = $approvalUrl;
        $metadata['paypal_order_status'] = is_string(data_get($data, 'status')) ? data_get($data, 'status') : 'CREATED';
        $payment->forceFill(['metadata' => $metadata])->save();

        return new CheckoutResult(
            checkoutUrl: $approvalUrl,
            providerReference: $orderId,
            expiresAt: $payment->expires_at?->toDateTimeImmutable(),
            metadata: ['provider' => 'paypal', 'flow' => 'orders_v2', 'order_status' => $metadata['paypal_order_status']],
        );
    }

    /** Retrieve the order and capture it only after PayPal reports buyer approval. */
    public function verifyPayment(Payment $payment): PaymentVerificationResult
    {
        $this->assertCredentialsAndCurrency($payment);
        $orderId = data_get($payment->metadata, 'paypal_order_id');

        if (! is_string($orderId) || ! preg_match('/^[A-Z0-9]{1,128}$/', $orderId)) {
            throw new PaymentGatewayException('The PayPal order reference is missing or invalid.');
        }

        $accessToken = $this->accessToken();
        $order = $this->getOrder($accessToken, $orderId);
        $this->assertOrderMatchesPayment($order, $payment, $orderId);

        $orderStatus = strtoupper((string) data_get($order, 'status'));
        if ($orderStatus === 'APPROVED') {
            $order = $this->captureOrder($accessToken, $payment, $orderId);
            $this->assertOrderMatchesPayment($order, $payment, $orderId);
            $orderStatus = strtoupper((string) data_get($order, 'status'));
        }

        if ($orderStatus === 'VOIDED') {
            return new PaymentVerificationResult('failed', null, $payment->amount_minor, $payment->currency, $orderStatus);
        }

        $captures = data_get($order, 'purchase_units.0.payments.captures', []);
        $capture = is_array($captures) ? ($captures[0] ?? null) : null;
        if (! is_array($capture)) {
            return new PaymentVerificationResult('pending', null, $payment->amount_minor, $payment->currency, ($orderStatus ?: 'UNKNOWN') . '_NO_CAPTURE');
        }

        $captureStatus = strtoupper((string) ($capture['status'] ?? ''));
        $captureAmount = data_get($capture, 'amount.value');
        $captureCurrency = data_get($capture, 'amount.currency_code');
        $amountMinor = $this->parseAmountMinor($captureAmount);

        if ($amountMinor !== $payment->amount_minor || $captureCurrency !== $payment->currency) {
            throw new PaymentGatewayException('PayPal capture amount or currency does not match the LearnQuest payment.');
        }

        $captureId = $capture['id'] ?? null;
        if ($captureId !== null && (! is_string($captureId) || $captureId === '' || strlen($captureId) > 150)) {
            throw new PaymentGatewayException('PayPal returned an invalid capture reference.');
        }

        return match ($captureStatus) {
            'COMPLETED' => new PaymentVerificationResult('succeeded', $captureId, $amountMinor, $captureCurrency, $captureStatus),
            'DECLINED', 'FAILED' => new PaymentVerificationResult('failed', $captureId, $amountMinor, $captureCurrency, $captureStatus),
            default => new PaymentVerificationResult('pending', $captureId, $amountMinor, $captureCurrency, $captureStatus ?: $orderStatus),
        };
    }

    /** Verify PayPal's transmission with the registered webhook ID, then normalize capture events. */
    public function parseWebhook(Request $request): VerifiedPaymentWebhook
    {
        $webhookId = $this->setting('webhook_id');
        if (! is_string($webhookId) || trim($webhookId) === '') {
            throw new PaymentGatewayUnavailableException('PayPal webhook ID is not configured.');
        }

        $payload = $request->json()->all();
        $headers = [
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
        ];

        foreach ($headers as $value) {
            if (! is_string($value) || trim($value) === '') {
                throw new WebhookAuthenticationException('PayPal webhook signature headers are incomplete.');
            }
        }

        $certUrl = (string) $headers['cert_url'];
        $certHost = strtolower((string) parse_url($certUrl, PHP_URL_HOST));
        if (parse_url($certUrl, PHP_URL_SCHEME) !== 'https' || ! str_ends_with($certHost, '.paypal.com')) {
            throw new WebhookAuthenticationException('PayPal webhook certificate URL is not trusted.');
        }

        if (! is_array($payload) || ! is_string($payload['id'] ?? null) || ! is_string($payload['event_type'] ?? null)) {
            throw new PaymentGatewayException('PayPal webhook event is malformed.');
        }

        $accessToken = $this->accessToken();
        
        // PayPal verifies the signature against the exact bytes it sent, so the
        // original body is embedded verbatim. Re-encoding the decoded array can
        // change those bytes (escaped slashes, {} becoming []) and fail valid events.
        $verificationBody = '{'
            . '"auth_algo":' . json_encode($headers['auth_algo'], JSON_THROW_ON_ERROR)
            . ',"cert_url":' . json_encode($headers['cert_url'], JSON_THROW_ON_ERROR)
            . ',"transmission_id":' . json_encode($headers['transmission_id'], JSON_THROW_ON_ERROR)
            . ',"transmission_sig":' . json_encode($headers['transmission_sig'], JSON_THROW_ON_ERROR)
            . ',"transmission_time":' . json_encode($headers['transmission_time'], JSON_THROW_ON_ERROR)
            . ',"webhook_id":' . json_encode($webhookId, JSON_THROW_ON_ERROR)
            . ',"webhook_event":' . $request->getContent()
            . '}';

        try {
            $verification = Http::acceptJson()
                ->withToken($accessToken)
                ->connectTimeout(5)
                ->timeout(20)
                ->withBody($verificationBody, 'application/json')
                ->send('POST', $this->baseUrl() . '/v1/notifications/verify-webhook-signature');
        } catch (ConnectionException $exception) {
            throw new PaymentGatewayUnavailableException('PayPal webhook signature verification could not be reached.', previous: $exception);
        }

        if (! $verification->successful()) {
            Log::warning('PayPal webhook signature verification API rejected the request.', [
                'http_status' => $verification->status(),
            ]);
            throw new PaymentGatewayUnavailableException('PayPal webhook signature verification service is unavailable.');
        }

        if (data_get($verification->json(), 'verification_status') !== 'SUCCESS') {
            throw new WebhookAuthenticationException('PayPal webhook signature verification failed.');
        }

        $eventType = $payload['event_type'];
        $resource = $payload['resource'] ?? null;
        if (! is_array($resource)) {
            throw new PaymentGatewayException('PayPal webhook resource is missing.');
        }

        $outcome = match ($eventType) {
            'PAYMENT.CAPTURE.COMPLETED' => 'succeeded',
            'PAYMENT.CAPTURE.PENDING' => 'pending',
            'PAYMENT.CAPTURE.DENIED' => 'failed',
            default => throw new PaymentGatewayException('Unsupported PayPal webhook event type.'),
        };

        $orderId = data_get($resource, 'supplementary_data.related_ids.order_id');
        $captureId = $resource['id'] ?? null;
        if (
            ! is_string($orderId) || ! preg_match('/^[A-Z0-9]{1,128}$/', $orderId)
            || ! is_string($captureId) || $captureId === '' || strlen($captureId) > 150
        ) {
            throw new PaymentGatewayException('PayPal capture event is missing valid order or capture identifiers.');
        }

        // Resolve the immutable LearnQuest reference from PayPal's order, not from the webhook body.
        $order = $this->getOrder($accessToken, $orderId);
        $units = data_get($order, 'purchase_units');
        if (data_get($order, 'id') !== $orderId || ! is_array($units) || count($units) !== 1 || ! is_array($units[0] ?? null)) {
            throw new PaymentGatewayException('PayPal webhook order could not be resolved.');
        }
        $unit = $units[0];
        $reference = $unit['custom_id'] ?? $unit['invoice_id'] ?? null;
        if (! is_string($reference) || $reference === '' || strlen($reference) > 64) {
            throw new PaymentGatewayException('PayPal order does not contain a LearnQuest payment reference.');
        }

        $eventAmount = $this->parseAmountMinor(data_get($resource, 'amount.value'));
        $eventCurrency = strtoupper((string) data_get($resource, 'amount.currency_code'));
        $orderAmount = $this->parseAmountMinor(data_get($unit, 'amount.value'));
        $orderCurrency = strtoupper((string) data_get($unit, 'amount.currency_code'));
        if ($eventAmount !== $orderAmount || $eventCurrency !== $orderCurrency || $eventCurrency !== 'USD') {
            throw new PaymentGatewayException('PayPal webhook capture does not match its order amount or currency.');
        }

        $captures = data_get($order, 'purchase_units.0.payments.captures', []);
        $captureMatches = is_array($captures) && collect($captures)->contains(
            fn($capture) =>
            is_array($capture) && ($capture['id'] ?? null) === $captureId
                && strtoupper((string) ($capture['status'] ?? '')) === strtoupper((string) ($resource['status'] ?? ''))
        );
        if (! $captureMatches) {
            throw new PaymentGatewayException('PayPal capture event is not present in the verified order.');
        }

        return new VerifiedPaymentWebhook(
            provider: 'paypal',
            eventId: $payload['id'],
            eventType: $eventType,
            paymentReference: $reference,
            providerTransactionId: $captureId,
            amountMinor: $eventAmount,
            currency: $eventCurrency,
            outcome: $outcome,
            signatureVerifiedAt: now()->toDateTimeImmutable(),
            metadata: ['channel' => 'paypal_webhook', 'provider_status' => strtoupper((string) ($resource['status'] ?? ''))],
            providerResourceId: $orderId,
        );
    }

    private function getOrder(string $accessToken, string $orderId): array
    {
        try {
            $response = Http::acceptJson()
                ->withToken($accessToken)
                ->connectTimeout(5)
                ->timeout(20)
                ->get($this->baseUrl() . '/v2/checkout/orders/' . rawurlencode($orderId));
        } catch (ConnectionException $exception) {
            throw new PaymentGatewayUnavailableException('PayPal order status could not be reached.', previous: $exception);
        }

        return $this->successfulJson($response->status(), $response->json(), 'PayPal order retrieval');
    }

    private function captureOrder(string $accessToken, Payment $payment, string $orderId): array
    {
        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withToken($accessToken)
                ->withHeaders([
                    'PayPal-Request-Id' => $this->requestId('capture', $payment),
                    'Prefer' => 'return=representation',
                ])
                ->connectTimeout(5)
                ->timeout(20)
                ->withBody('{}', 'application/json')
                ->send('POST', $this->baseUrl() . '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture');
        } catch (ConnectionException $exception) {
            // A timed-out capture is ambiguous. A later reconciliation first GETs
            // the order and will observe an already-completed capture if it landed.
            throw new PaymentGatewayUnavailableException('PayPal capture could not be confirmed. Please retry payment verification shortly.', previous: $exception);
        }

        return $this->successfulJson($response->status(), $response->json(), 'PayPal order capture');
    }

    private function assertOrderMatchesPayment(array $order, Payment $payment, string $orderId): void
    {
        if (data_get($order, 'id') !== $orderId) {
            throw new PaymentGatewayException('PayPal returned a different order than the LearnQuest payment.');
        }

        $purchaseUnits = data_get($order, 'purchase_units');
        if (! is_array($purchaseUnits) || count($purchaseUnits) !== 1 || ! is_array($purchaseUnits[0] ?? null)) {
            throw new PaymentGatewayException('PayPal order does not contain the expected purchase unit.');
        }

        $unit = $purchaseUnits[0];
        $reference = $unit['custom_id'] ?? $unit['invoice_id'] ?? null;
        if (! is_string($reference) || ! hash_equals($payment->reference, $reference)) {
            throw new PaymentGatewayException('PayPal order reference does not match the LearnQuest payment.');
        }

        $amountMinor = $this->parseAmountMinor(data_get($unit, 'amount.value'));
        if (
            $amountMinor !== $payment->amount_minor
            || data_get($unit, 'amount.currency_code') !== $payment->currency
        ) {
            throw new PaymentGatewayException('PayPal order amount or currency does not match the LearnQuest payment.');
        }
    }

    private function accessToken(): string
    {
        try {
            $response = Http::acceptJson()
                ->asForm()
                ->withBasicAuth((string) $this->setting('client_id'), (string) $this->setting('client_secret'))
                ->connectTimeout(5)
                ->timeout(20)
                ->post($this->baseUrl() . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);
        } catch (ConnectionException $exception) {
            throw new PaymentGatewayUnavailableException('PayPal authentication could not be reached.', previous: $exception);
        }

        $data = $response->json();
        if (! $response->successful()) {
            Log::warning('PayPal OAuth request failed', ['http_status' => $response->status()]);
            throw new PaymentGatewayUnavailableException('PayPal authentication service is unavailable.');
        }

        if (
            ! is_array($data)
            || ! is_string($data['access_token'] ?? null)
            || $data['access_token'] === ''
            || ! is_string($data['token_type'] ?? null)
            || strtolower($data['token_type']) !== 'bearer'
        ) {
            Log::warning('PayPal OAuth request failed', ['http_status' => $response->status()]);
            throw new PaymentGatewayException('PayPal authentication failed or returned an invalid response.');
        }

        return $data['access_token'];
    }

    private function assertCredentialsAndCurrency(Payment $payment): void
    {
        if ($payment->provider !== 'paypal' || $payment->currency !== 'USD') {
            throw new PaymentGatewayException('PayPal checkout currently supports USD payments only.');
        }

        $clientId = $this->setting('client_id');
        $clientSecret = $this->setting('client_secret');
        if (! is_string($clientId) || trim($clientId) === '' || ! is_string($clientSecret) || trim($clientSecret) === '') {
            throw new PaymentGatewayUnavailableException('PayPal Sandbox credentials are not configured.');
        }

        $this->baseUrl();
    }

    private function baseUrl(): string
    {
        $baseUrl = rtrim((string) $this->setting('base_url'), '/');
        if (! in_array($baseUrl, self::ALLOWED_BASE_URLS, true)) {
            throw new PaymentGatewayUnavailableException('PayPal API base URL must be an approved HTTPS PayPal endpoint.');
        }

        return $baseUrl;
    }

    private function successfulJson(int $status, mixed $data, string $operation): array
    {
        if ($status < 200 || $status >= 300) {
            Log::warning('PayPal API request failed', ['operation' => $operation, 'http_status' => $status]);
            $this->throwIfProviderUnavailable($status, $operation);
            throw new PaymentGatewayException('PayPal request was rejected.');
        }

        if (! is_array($data)) {
            Log::warning('PayPal API returned an invalid response', ['operation' => $operation]);
            throw new PaymentGatewayException('PayPal returned an invalid response.');
        }

        return $data;
    }

    /** Treat rate limits and provider-side server errors as retryable outages. */
    private function throwIfProviderUnavailable(int $status, string $operation): void
    {
        if ($status === 429 || $status >= 500) {
            throw new PaymentGatewayUnavailableException(sprintf('%s is temporarily unavailable (HTTP %d).', $operation, $status));
        }
    }

    private function isTrustedApprovalUrl(mixed $url): bool
    {
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            return false;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), [
            'www.sandbox.paypal.com',
            'www.paypal.com',
        ], true);
    }

    private function formatAmount(int $amountMinor): string
    {
        if ($amountMinor <= 0) {
            throw new PaymentGatewayException('PayPal payment amount must be greater than zero.');
        }

        return intdiv($amountMinor, 100) . '.' . str_pad((string) ($amountMinor % 100), 2, '0', STR_PAD_LEFT);
    }

    private function parseAmountMinor(mixed $amount): int
    {
        if (! is_string($amount) || ! preg_match('/^(0|[1-9][0-9]*)\.([0-9]{2})$/', $amount, $matches)) {
            throw new PaymentGatewayException('PayPal returned an invalid monetary amount.');
        }

        $whole = filter_var($matches[1], FILTER_VALIDATE_INT);
        if ($whole === false || $whole > intdiv(PHP_INT_MAX, 100)) {
            throw new PaymentGatewayException('PayPal returned an out-of-range monetary amount.');
        }

        return ($whole * 100) + (int) $matches[2];
    }

    private function requestId(string $operation, Payment $payment): string
    {
        return substr(hash('sha256', 'learnquest-paypal-' . $operation . ':' . $payment->reference), 0, 32);
    }

    private function setting(string $key): mixed
    {
        return config("learnquest.gateways.providers.paypal.{$key}");
    }
}
