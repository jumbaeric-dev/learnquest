<?php

namespace Tests\Feature\Billing;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\User;
use App\Services\Billing\Gateways\IntaSendGateway;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IntaSendGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('parent', 'web');
        $this->seed(PlanSeeder::class);
        config([
            'learnquest.gateways.providers.intasend.enabled' => true,
            'learnquest.gateways.providers.intasend.secret_key' => 'ISSecretKey_test_example',
            'learnquest.gateways.providers.intasend.public_key' => 'ISPubKey_test_example',
            'learnquest.gateways.providers.intasend.webhook_secret' => 'learnquest-test-challenge',
            'learnquest.gateways.providers.intasend.base_url' => 'https://api.intasend.com',
        ]);
    }

    private function payment(): Payment
    {
        $parent = User::factory()->create(['name' => 'Jane Parent']);
        $parent->assignRole('parent');
        $price = PlanPrice::query()->where('currency', 'KES')->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))->firstOrFail();

        return Payment::create([
            'user_id' => $parent->id,
            'plan_id' => $price->plan_id,
            'plan_price_id' => $price->id,
            'plan_key' => 'premium',
            'duration_months' => 1,
            'reference' => 'LQ-INTASEND-TEST-0001',
            'provider' => 'intasend',
            'amount_minor' => $price->amount_minor,
            'currency' => 'KES',
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    public function test_hosted_checkout_creates_provider_session_and_stores_invoice_reference(): void
    {
        $payment = $this->payment();
        Http::fake(['https://api.intasend.com/api/v1/checkout/' => Http::response([
            'id' => 'INV-123', 'url' => 'https://sandbox.intasend.com/checkout/INV-123/express/', 'paid' => false,
        ])]);

        $result = app(IntaSendGateway::class)->initiateCheckout($payment);

        $this->assertSame('https://sandbox.intasend.com/checkout/INV-123/express/', $result->checkoutUrl);
        $this->assertSame('INV-123', $result->providerReference);
        $this->assertSame('INV-123', data_get($payment->fresh()->metadata, 'intasend_invoice_id'));
        Http::assertSent(fn ($request) => $request->url() === 'https://api.intasend.com/api/v1/checkout/'
            && $request->hasHeader('X-IntaSend-Public-API-Key', 'ISPubKey_test_example')
            && $request['api_ref'] === $payment->reference);
    }

    public function test_mpesa_stk_push_normalizes_phone_and_returns_push_interaction(): void
    {
        $payment = $this->payment();
        Http::fake(['https://api.intasend.com/api/v1/payment/mpesa-stk-push/' => Http::response([
            'invoice' => ['invoice_id' => 'INV-STK-1', 'state' => 'PENDING'],
        ])]);

        $result = app(IntaSendGateway::class)->initiateMpesaStkPush($payment, '0712345678');

        $this->assertSame('push', $result->interaction);
        $this->assertNull($result->checkoutUrl);
        $this->assertSame('INV-STK-1', data_get($payment->fresh()->metadata, 'intasend_invoice_id'));
        Http::assertSent(fn ($request) => $request['phone_number'] === '254712345678'
            && $request['api_ref'] === $payment->reference);
    }

    public function test_mpesa_stk_push_rejects_invalid_phone_number(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(IntaSendGateway::class)->initiateMpesaStkPush($this->payment(), '12345');
    }

    public function test_webhook_challenge_is_compared_before_event_is_trusted(): void
    {
        $request = request()->create('/api/webhooks/intasend', 'POST', [], [], [], [], json_encode([
            'topic' => 'collection_event', 'challenge' => 'wrong', 'invoice_id' => 'INV-1',
            'api_ref' => 'LQ-INTASEND-TEST-0001', 'state' => 'COMPLETE', 'value' => '499.00', 'currency' => 'KES',
        ]));
        $request->headers->set('Content-Type', 'application/json');

        $this->expectException(PaymentGatewayException::class);
        app(IntaSendGateway::class)->parseWebhook($request);
    }

    public function test_webhook_challenge_normalizes_collection_event(): void
    {
        $request = request()->create('/api/webhooks/intasend', 'POST', [], [], [], [], json_encode([
            'topic' => 'collection_event', 'challenge' => 'learnquest-test-challenge',
            'invoice_id' => 'INV-1', 'api_ref' => 'LQ-INTASEND-TEST-0001', 'state' => 'COMPLETE',
            'provider_ref' => 'MPESA-RECEIPT-1', 'value' => '499.00', 'currency' => 'KES', 'provider' => 'M-PESA',
        ]));
        $request->headers->set('Content-Type', 'application/json');

        $webhook = app(IntaSendGateway::class)->parseWebhook($request);

        $this->assertSame('intasend', $webhook->provider);
        $this->assertSame('INV-1:COMPLETE', $webhook->eventId);
        $this->assertSame(49900, $webhook->amountMinor);
        $this->assertSame('succeeded', $webhook->outcome);
        $this->assertSame('MPESA-RECEIPT-1', $webhook->providerTransactionId);
    }

    public function test_webhook_controller_checks_intasend_status_before_settlement(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['intasend_invoice_id' => 'INV-WEBHOOK-1']])->save();
        Http::fake(['https://api.intasend.com/api/v1/payment/status/' => Http::response([
            'invoice' => [
                'invoice_id' => 'INV-WEBHOOK-1', 'api_ref' => $payment->reference,
                'state' => 'COMPLETE', 'value' => '499.00', 'currency' => 'KES', 'provider_ref' => 'MPESA-WEBHOOK-1',
            ],
        ])]);

        $response = $this->postJson(route('webhooks.intasend'), [
            'topic' => 'collection_event', 'challenge' => 'learnquest-test-challenge',
            'invoice_id' => 'INV-WEBHOOK-1', 'api_ref' => $payment->reference,
            'state' => 'COMPLETE', 'value' => '499.00', 'currency' => 'KES',
            'provider_ref' => 'MPESA-WEBHOOK-1', 'provider' => 'M-PESA',
        ]);

        $response->assertOk()->assertJson(['status' => 'succeeded']);
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
    }

    public function test_payment_status_is_verified_against_invoice_and_internal_reference(): void
    {
        $payment = $this->payment();
        $payment->forceFill(['metadata' => ['intasend_invoice_id' => 'INV-STATUS-1']])->save();
        Http::fake(['https://api.intasend.com/api/v1/payment/status/' => Http::response([
            'invoice' => [
                'invoice_id' => 'INV-STATUS-1', 'api_ref' => $payment->reference,
                'state' => 'COMPLETE', 'value' => '499.00', 'currency' => 'KES', 'provider_ref' => 'MPESA-1',
            ],
        ])]);

        $result = app(IntaSendGateway::class)->verifyPayment($payment->fresh());

        $this->assertSame('succeeded', $result->outcome);
        $this->assertSame(49900, $result->amountMinor);
        $this->assertSame('MPESA-1', $result->providerTransactionId);
    }
}
