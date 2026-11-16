<?php

namespace Tests\Feature\Billing;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Exceptions\Billing\WebhookAuthenticationException;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\User;
use App\Services\Billing\Gateways\PaystackGateway;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaystackGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('parent', 'web');
        $this->seed(PlanSeeder::class);
        config([
            'learnquest.gateways.providers.paystack.enabled' => true,
            'learnquest.gateways.providers.paystack.currencies' => ['KES', 'USD'],
            'learnquest.gateways.providers.paystack.secret_key' => 'sk_test_learnquest_test_key',
            'learnquest.gateways.providers.paystack.webhook_secret' => 'whsec_learnquest_test_secret',
        ]);
    }

    private function payment(): Payment
    {
        $parent = User::factory()->create(['name' => 'Jane Parent', 'email' => 'jane@example.test']);
        $parent->assignRole('parent');
        $price = PlanPrice::query()->where('currency', 'KES')->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))->firstOrFail();

        return Payment::create([
            'user_id' => $parent->id,
            'plan_id' => $price->plan_id,
            'plan_price_id' => $price->id,
            'plan_key' => 'premium',
            'duration_months' => 1,
            'reference' => 'LQ-PAYSTACK-TEST-0001',
            'provider' => 'paystack',
            'amount_minor' => $price->amount_minor,
            'currency' => 'KES',
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    public function test_checkout_initializes_transaction_in_minor_units_and_stores_hosted_url(): void
    {
        $payment = $this->payment();
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'message' => 'Authorization URL created',
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/test-access-code',
                    'access_code' => 'test-access-code',
                    'reference' => $payment->reference,
                ],
            ]),
        ]);

        $result = app(PaystackGateway::class)->initiateCheckout($payment);

        $this->assertSame('https://checkout.paystack.com/test-access-code', $result->checkoutUrl);
        $this->assertSame($payment->reference, $result->providerReference);
        $this->assertSame('https://checkout.paystack.com/test-access-code', data_get($payment->fresh()->metadata, 'paystack_checkout_url'));
        $this->assertSame('test-access-code', data_get($payment->fresh()->metadata, 'paystack_access_code'));

        Http::assertSent(fn ($request) => $request->url() === 'https://api.paystack.co/transaction/initialize'
            && $request->hasHeader('Authorization', 'Bearer sk_test_learnquest_test_key')
            && (string) $request['amount'] === (string) $payment->amount_minor
            && $request['currency'] === 'KES'
            && $request['reference'] === $payment->reference);
    }

    public function test_verification_returns_provider_confirmed_transaction_details(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['paystack_transaction_reference' => $payment->reference]])->save();
        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 123456,
                    'status' => 'success',
                    'reference' => $payment->reference,
                    'amount' => $payment->amount_minor,
                    'currency' => 'KES',
                ],
            ]),
        ]);

        $result = app(PaystackGateway::class)->verifyPayment($payment);

        $this->assertSame('succeeded', $result->outcome);
        $this->assertSame('123456', $result->providerTransactionId);
        $this->assertSame($payment->amount_minor, $result->amountMinor);
        $this->assertSame('KES', $result->currency);
    }

    public function test_verification_rejects_a_different_internal_reference(): void
    {
        $payment = $this->payment();
        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 123456,
                    'status' => 'success',
                    'reference' => 'LQ-ANOTHER-PAYMENT',
                    'amount' => $payment->amount_minor,
                    'currency' => 'KES',
                ],
            ]),
        ]);

        $this->expectException(PaymentGatewayException::class);
        app(PaystackGateway::class)->verifyPayment($payment);
    }

    public function test_webhook_requires_valid_legacy_signature_and_normalizes_charge_success(): void
    {
        $payment = $this->payment();
        $body = json_encode([
            'event' => 'charge.success',
            'data' => [
                'id' => 998877,
                'status' => 'success',
                'reference' => $payment->reference,
                'amount' => $payment->amount_minor,
                'currency' => 'KES',
                'channel' => 'card',
            ],
        ], JSON_THROW_ON_ERROR);
        $request = Request::create('/api/webhooks/paystack', 'POST', [], [], [], [], $body);
        $request->headers->set('x-paystack-signature', hash_hmac('sha512', $body, 'sk_test_learnquest_test_key'));

        $webhook = app(PaystackGateway::class)->parseWebhook($request);

        $this->assertSame('paystack', $webhook->provider);
        $this->assertSame($payment->reference, $webhook->paymentReference);
        $this->assertSame($payment->amount_minor, $webhook->amountMinor);
        $this->assertSame('succeeded', $webhook->outcome);
        $this->assertSame('998877', $webhook->providerTransactionId);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $body = '{"event":"charge.success","data":{}}';
        $request = Request::create('/api/webhooks/paystack', 'POST', [], [], [], [], $body);
        $request->headers->set('x-paystack-signature', str_repeat('a', 128));

        $this->expectException(WebhookAuthenticationException::class);
        app(PaystackGateway::class)->parseWebhook($request);
    }

    public function test_webhook_supports_timestamped_paystack_signing_secret(): void
    {
        $payment = $this->payment();
        $body = json_encode([
            'event' => 'charge.success',
            'data' => [
                'id' => 998878,
                'reference' => $payment->reference,
                'amount' => $payment->amount_minor,
                'currency' => 'KES',
            ],
        ], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha512', $timestamp . '.' . $body, 'whsec_learnquest_test_secret');
        $request = Request::create('/api/webhooks/paystack', 'POST', [], [], [], [], $body);
        $request->headers->set('Paystack-Signature', "t={$timestamp},v1=whk_test:{$signature}");

        $webhook = app(PaystackGateway::class)->parseWebhook($request);

        $this->assertSame($payment->reference, $webhook->paymentReference);
    }

    public function test_signed_success_webhook_only_settles_after_server_side_verification(): void
    {
        $payment = $this->payment();
        $body = json_encode([
            'event' => 'charge.success',
            'data' => [
                'id' => 112233,
                'status' => 'success',
                'reference' => $payment->reference,
                'amount' => $payment->amount_minor,
                'currency' => 'KES',
            ],
        ], JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha512', $body, 'sk_test_learnquest_test_key');

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 112233,
                    'status' => 'success',
                    'reference' => $payment->reference,
                    'amount' => $payment->amount_minor,
                    'currency' => 'KES',
                ],
            ]),
        ]);

        $response = $this->call(
            'POST',
            route('webhooks.paystack'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYSTACK_SIGNATURE' => $signature],
            $body,
        );

        $response->assertOk();
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/transaction/verify/'));
    }

    public function test_signed_success_webhook_does_not_settle_when_paystack_reports_pending(): void
    {
        $payment = $this->payment();
        $body = json_encode([
            'event' => 'charge.success',
            'data' => [
                'id' => 112234,
                'reference' => $payment->reference,
                'amount' => $payment->amount_minor,
                'currency' => 'KES',
            ],
        ], JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha512', $body, 'sk_test_learnquest_test_key');

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 112234,
                    'status' => 'pending',
                    'reference' => $payment->reference,
                    'amount' => $payment->amount_minor,
                    'currency' => 'KES',
                ],
            ]),
        ]);

        $response = $this->call(
            'POST',
            route('webhooks.paystack'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYSTACK_SIGNATURE' => $signature],
            $body,
        );

        $response->assertOk();
        $this->assertSame(Payment::STATUS_PROCESSING, $payment->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
        $this->assertDatabaseCount('subscriptions', 0);
    }

}
