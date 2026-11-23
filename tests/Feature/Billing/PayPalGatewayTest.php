<?php

namespace Tests\Feature\Billing;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\User;
use App\Services\Billing\Gateways\PayPalGateway;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayPalGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('parent', 'web');
        $this->seed(PlanSeeder::class);
        config([
            'learnquest.gateways.providers.paypal.enabled' => true,
            'learnquest.gateways.providers.paypal.currencies' => ['USD'],
            'learnquest.gateways.providers.paypal.client_id' => 'sandbox-client-id',
            'learnquest.gateways.providers.paypal.client_secret' => 'sandbox-client-secret',
            'learnquest.gateways.providers.paypal.base_url' => 'https://api-m.sandbox.paypal.com',
            'learnquest.gateways.providers.paypal.webhook_id' => 'SANDBOX-WEBHOOK-ID',
        ]);
    }

    private function payment(string $currency = 'USD'): Payment
    {
        $unique = Str::uuid()->toString();
        $parent = User::factory()->create(['name' => 'Jane Parent', 'email' => 'jane+' . $unique . '@example.test']);
        $parent->assignRole('parent');
        $price = PlanPrice::query()->where('currency', $currency)->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))->firstOrFail();

        return Payment::create([
            'user_id' => $parent->id,
            'plan_id' => $price->plan_id,
            'plan_price_id' => $price->id,
            'plan_key' => 'premium',
            'duration_months' => 1,
            'reference' => 'LQ-PAYPAL-TEST-' . strtoupper(Str::random(12)),
            'provider' => 'paypal',
            'amount_minor' => $price->amount_minor,
            'currency' => $currency,
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    public function test_checkout_authenticates_and_creates_usd_order_with_hosted_approval_url(): void
    {
        $payment = $this->payment();
        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer', 'expires_in' => 900], 200)
            ->push([
                'id' => '5O190127TN364715T',
                'status' => 'CREATED',
                'links' => [
                    ['rel' => 'self', 'href' => 'https://api-m.sandbox.paypal.com/v2/checkout/orders/5O190127TN364715T'],
                    ['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=5O190127TN364715T'],
                ],
            ], 201);

        $result = app(PayPalGateway::class)->initiateCheckout($payment);

        $this->assertSame('https://www.sandbox.paypal.com/checkoutnow?token=5O190127TN364715T', $result->checkoutUrl);
        $this->assertSame('5O190127TN364715T', $result->providerReference);
        $this->assertSame('5O190127TN364715T', data_get($payment->fresh()->metadata, 'paypal_order_id'));
        $this->assertSame($result->checkoutUrl, data_get($payment->fresh()->metadata, 'paypal_checkout_url'));

        Http::assertSent(fn ($request) => $request->url() === 'https://api-m.sandbox.paypal.com/v1/oauth2/token'
            && $request->hasHeader('Authorization', 'Basic ' . base64_encode('sandbox-client-id:sandbox-client-secret'))
            && $request['grant_type'] === 'client_credentials');

        Http::assertSent(fn ($request) => $request->url() === 'https://api-m.sandbox.paypal.com/v2/checkout/orders'
            && $request->hasHeader('Authorization', 'Bearer sandbox-access-token')
            && $request->hasHeader('PayPal-Request-Id')
            && $request['intent'] === 'CAPTURE'
            && $request['purchase_units'][0]['reference_id'] === $payment->reference
            && $request['purchase_units'][0]['amount']['currency_code'] === 'USD'
            && $request['purchase_units'][0]['amount']['value'] === number_format($payment->amount_minor / 100, 2, '.', ''));
    }

    public function test_checkout_rejects_non_usd_payment_before_contacting_paypal(): void
    {
        $payment = $this->payment('KES');
        Http::fake();

        $this->expectException(PaymentGatewayException::class);
        app(PayPalGateway::class)->initiateCheckout($payment);
    }

    public function test_checkout_rejects_untrusted_approval_url(): void
    {
        $payment = $this->payment();
        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer'], 200)
            ->push([
                'id' => '5O190127TN364715T',
                'status' => 'CREATED',
                'links' => [['rel' => 'approve', 'href' => 'https://attacker.example/checkout']],
            ], 201);

        $this->expectException(PaymentGatewayException::class);
        app(PayPalGateway::class)->initiateCheckout($payment);
    }

    public function test_checkout_rejects_unapproved_api_base_url(): void
    {
        $payment = $this->payment();
        config(['learnquest.gateways.providers.paypal.base_url' => 'https://example.com']);
        Http::fake();

        $this->expectException(\App\Exceptions\Billing\PaymentGatewayUnavailableException::class);
        app(PayPalGateway::class)->initiateCheckout($payment);
    }
    public function test_approved_order_is_captured_and_only_completed_capture_is_reported_as_success(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['paypal_order_id' => '5O190127TN364715T']])->save();
        $amount = intdiv($payment->amount_minor, 100) . '.' . str_pad((string) ($payment->amount_minor % 100), 2, '0', STR_PAD_LEFT);

        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer'], 200)
            ->push([
                'id' => '5O190127TN364715T',
                'status' => 'APPROVED',
                'purchase_units' => [[
                    'custom_id' => $payment->reference,
                    'amount' => ['currency_code' => 'USD', 'value' => $amount],
                ]],
            ], 200)
            ->push([
                'id' => '5O190127TN364715T',
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'custom_id' => $payment->reference,
                    'amount' => ['currency_code' => 'USD', 'value' => $amount],
                    'payments' => ['captures' => [[
                        'id' => 'PAYPAL-CAPTURE-456',
                        'status' => 'COMPLETED',
                        'amount' => ['currency_code' => 'USD', 'value' => $amount],
                    ]]],
                ]],
            ], 201);

        $result = app(PayPalGateway::class)->verifyPayment($payment);

        $this->assertSame('succeeded', $result->outcome);
        $this->assertSame('PAYPAL-CAPTURE-456', $result->providerTransactionId);
        $this->assertSame($payment->amount_minor, $result->amountMinor);
        $this->assertSame('USD', $result->currency);

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request->url() === 'https://api-m.sandbox.paypal.com/v2/checkout/orders/5O190127TN364715T');
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'https://api-m.sandbox.paypal.com/v2/checkout/orders/5O190127TN364715T/capture'
            && $request->hasHeader('PayPal-Request-Id'));
    }

    public function test_completed_order_is_verified_without_attempting_a_second_capture(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['paypal_order_id' => '5O190127TN364715T']])->save();
        $amount = intdiv($payment->amount_minor, 100) . '.' . str_pad((string) ($payment->amount_minor % 100), 2, '0', STR_PAD_LEFT);

        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer'], 200)
            ->push([
                'id' => '5O190127TN364715T',
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'custom_id' => $payment->reference,
                    'amount' => ['currency_code' => 'USD', 'value' => $amount],
                    'payments' => ['captures' => [[
                        'id' => 'PAYPAL-CAPTURE-456',
                        'status' => 'COMPLETED',
                        'amount' => ['currency_code' => 'USD', 'value' => $amount],
                    ]]],
                ]],
            ], 200);

        $result = app(PayPalGateway::class)->verifyPayment($payment);

        $this->assertSame('succeeded', $result->outcome);
        Http::assertSentCount(2);
    }

    public function test_order_reference_mismatch_is_rejected_before_capture(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['paypal_order_id' => '5O190127TN364715T']])->save();
        $amount = intdiv($payment->amount_minor, 100) . '.' . str_pad((string) ($payment->amount_minor % 100), 2, '0', STR_PAD_LEFT);

        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer'], 200)
            ->push([
                'id' => '5O190127TN364715T',
                'status' => 'APPROVED',
                'purchase_units' => [[
                    'custom_id' => 'LQ-SOMEONE-ELSE',
                    'amount' => ['currency_code' => 'USD', 'value' => $amount],
                ]],
            ], 200);

        $this->expectException(PaymentGatewayException::class);
        try {
            app(PayPalGateway::class)->verifyPayment($payment);
        } finally {
            Http::assertSentCount(2);
        }
    }

    public function test_order_without_buyer_approval_remains_pending_and_is_not_captured(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['paypal_order_id' => '5O190127TN364715T']])->save();
        $amount = intdiv($payment->amount_minor, 100) . '.' . str_pad((string) ($payment->amount_minor % 100), 2, '0', STR_PAD_LEFT);

        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer'], 200)
            ->push([
                'id' => '5O190127TN364715T',
                'status' => 'CREATED',
                'purchase_units' => [[
                    'custom_id' => $payment->reference,
                    'amount' => ['currency_code' => 'USD', 'value' => $amount],
                ]],
            ], 200);

        $result = app(PayPalGateway::class)->verifyPayment($payment);

        $this->assertSame('pending', $result->outcome);
        $this->assertSame('CREATED_NO_CAPTURE', $result->providerState);
        Http::assertSentCount(2);
    }



    public function test_pending_capture_is_not_reported_as_success(): void
    {
        $this->assertCaptureOutcome('PENDING', 'pending');
    }

    public function test_declined_capture_is_reported_as_failed(): void
    {
        $this->assertCaptureOutcome('DECLINED', 'failed');
    }

    private function assertCaptureOutcome(string $captureStatus, string $expectedOutcome): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['paypal_order_id' => '5O190127TN364715T']])->save();
        $amount = intdiv($payment->amount_minor, 100) . '.' . str_pad((string) ($payment->amount_minor % 100), 2, '0', STR_PAD_LEFT);

        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer'], 200)
            ->push([
                'id' => '5O190127TN364715T',
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'custom_id' => $payment->reference,
                    'amount' => ['currency_code' => 'USD', 'value' => $amount],
                    'payments' => ['captures' => [[
                        'id' => 'PAYPAL-CAPTURE-STATUS',
                        'status' => $captureStatus,
                        'amount' => ['currency_code' => 'USD', 'value' => $amount],
                    ]]],
                ]],
            ], 200);

        $result = app(PayPalGateway::class)->verifyPayment($payment);

        $this->assertSame($expectedOutcome, $result->outcome);
        $this->assertSame($captureStatus, $result->providerState);
        Http::assertSentCount(2);
    }

    public function test_capture_amount_mismatch_is_rejected(): void
    {
        $this->assertCaptureMismatch(['value' => '1.00', 'currency_code' => 'USD']);
    }

    public function test_capture_currency_mismatch_is_rejected(): void
    {
        $this->assertCaptureMismatch(['currency_code' => 'EUR']);
    }

    private function assertCaptureMismatch(array $captureAmount): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['paypal_order_id' => '5O190127TN364715T']])->save();
        $expectedAmount = intdiv($payment->amount_minor, 100) . '.' . str_pad((string) ($payment->amount_minor % 100), 2, '0', STR_PAD_LEFT);
        $captureAmount['value'] ??= $expectedAmount;

        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer'], 200)
            ->push([
                'id' => '5O190127TN364715T',
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'custom_id' => $payment->reference,
                    'amount' => ['currency_code' => 'USD', 'value' => $expectedAmount],
                    'payments' => ['captures' => [[
                        'id' => 'PAYPAL-CAPTURE-MISMATCH',
                        'status' => 'COMPLETED',
                        'amount' => $captureAmount,
                    ]]],
                ]],
            ], 200);

        try {
            app(PayPalGateway::class)->verifyPayment($payment);
            $this->fail('A mismatched capture amount or currency must be rejected.');
        } catch (PaymentGatewayException) {
            $this->assertTrue(true);
        }

        Http::assertSentCount(2);
    }

    public function test_paypal_server_errors_are_classified_as_temporary_gateway_unavailability(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['paypal_order_id' => '5O190127TN364715T']])->save();

        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer'], 200)
            ->push(['message' => 'temporarily unavailable'], 503);

        $this->expectException(\App\Exceptions\Billing\PaymentGatewayUnavailableException::class);
        app(PayPalGateway::class)->verifyPayment($payment);
    }

    public function test_capture_webhook_verifies_signature_and_resolves_reference_from_paypal_order(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['paypal_order_id' => '5O190127TN364715T']])->save();
        $amount = intdiv($payment->amount_minor, 100) . '.' . str_pad((string) ($payment->amount_minor % 100), 2, '0', STR_PAD_LEFT);
        $event = [
            'id' => 'WH-TEST-EVENT-0001',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE123456', 'status' => 'COMPLETED',
                'amount' => ['value' => $amount, 'currency_code' => 'USD'],
                'supplementary_data' => ['related_ids' => ['order_id' => '5O190127TN364715T']],
            ],
        ];
        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer'], 200)
            ->push(['verification_status' => 'SUCCESS'], 200)
            ->push([
                'id' => '5O190127TN364715T', 'status' => 'COMPLETED',
                'purchase_units' => [[
                    'custom_id' => $payment->reference,
                    'amount' => ['currency_code' => 'USD', 'value' => $amount],
                    'payments' => ['captures' => [[
                        'id' => 'CAPTURE123456', 'status' => 'COMPLETED',
                        'amount' => ['currency_code' => 'USD', 'value' => $amount],
                    ]]],
                ]],
            ], 200);

        $request = \Illuminate\Http\Request::create('/api/webhooks/paypal', 'POST', [], [], [], [
            'HTTP_PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            'HTTP_PAYPAL-CERT-URL' => 'https://api-m.sandbox.paypal.com/cert/test',
            'HTTP_PAYPAL-TRANSMISSION-ID' => 'transmission-123',
            'HTTP_PAYPAL-TRANSMISSION-SIG' => 'signature-value',
            'HTTP_PAYPAL-TRANSMISSION-TIME' => '2026-11-12T10:00:00Z',
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($event));

        $webhook = app(PayPalGateway::class)->parseWebhook($request);

        $this->assertSame('paypal', $webhook->provider);
        $this->assertSame($payment->reference, $webhook->paymentReference);
        $this->assertSame('5O190127TN364715T', $webhook->providerResourceId);
        $this->assertSame('CAPTURE123456', $webhook->providerTransactionId);
        $this->assertSame('succeeded', $webhook->outcome);
        Http::assertSent(fn ($sent) => str_ends_with($sent->url(), '/v1/notifications/verify-webhook-signature')
            && $sent['webhook_id'] === 'SANDBOX-WEBHOOK-ID'
            && $sent['webhook_event']['id'] === 'WH-TEST-EVENT-0001');
    }

    public function test_capture_webhook_rejects_failed_paypal_signature(): void
    {
        Http::fakeSequence()
            ->push(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer'], 200)
            ->push(['verification_status' => 'FAILURE'], 200);

        $request = \Illuminate\Http\Request::create('/api/webhooks/paypal', 'POST', [], [], [], [
            'HTTP_PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            'HTTP_PAYPAL-CERT-URL' => 'https://api-m.sandbox.paypal.com/cert/test',
            'HTTP_PAYPAL-TRANSMISSION-ID' => 'transmission-123',
            'HTTP_PAYPAL-TRANSMISSION-SIG' => 'bad-signature',
            'HTTP_PAYPAL-TRANSMISSION-TIME' => '2026-11-12T10:00:00Z',
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['id' => 'WH-BAD', 'event_type' => 'PAYMENT.CAPTURE.COMPLETED', 'resource' => []]));

        $this->expectException(\App\Exceptions\Billing\WebhookAuthenticationException::class);
        app(PayPalGateway::class)->parseWebhook($request);
    }

}
