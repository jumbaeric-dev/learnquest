<?php

namespace Tests\Feature\Billing;

use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayPalWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://api-m.sandbox.paypal.com';
    private const ORDER_ID = '5O190127TN364715T';

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
            'learnquest.gateways.providers.paypal.base_url' => self::BASE_URL,
            'learnquest.gateways.providers.paypal.webhook_id' => 'SANDBOX-WEBHOOK-ID',
        ]);
    }

    private function payment(string $reference = 'LQ-PAYPAL-WEBHOOK-0001', string $provider = 'paypal', array $metadata = []): Payment
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        $price = PlanPrice::query()->where('currency', 'USD')->where('months', 1)
            ->whereHas('plan', fn($query) => $query->where('key', 'premium'))->firstOrFail();

        return Payment::create([
            'user_id' => $parent->id,
            'plan_id' => $price->plan_id,
            'plan_price_id' => $price->id,
            'plan_key' => 'premium',
            'duration_months' => 1,
            'reference' => $reference,
            'provider' => $provider,
            'amount_minor' => $price->amount_minor,
            'currency' => 'USD',
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addMinutes(30),
            'metadata' => $metadata,
        ]);
    }

    private function event(Payment $payment, array $overrides = [], string $orderId = self::ORDER_ID): array
    {
        $amount = intdiv($payment->amount_minor, 100) . '.' . str_pad((string) ($payment->amount_minor % 100), 2, '0', STR_PAD_LEFT);

        return array_replace_recursive([
            'id' => 'WH-CONTROLLER-TEST-0001',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE-CONTROLLER-0001',
                'status' => 'COMPLETED',
                'amount' => ['value' => $amount, 'currency_code' => 'USD'],
                'supplementary_data' => ['related_ids' => ['order_id' => $orderId]],
            ],
        ], $overrides);
    }

    private function order(Payment $payment, string $orderId = self::ORDER_ID): array
    {
        $amount = intdiv($payment->amount_minor, 100) . '.' . str_pad((string) ($payment->amount_minor % 100), 2, '0', STR_PAD_LEFT);

        return [
            'id' => $orderId,
            'status' => 'COMPLETED',
            'purchase_units' => [[
                'custom_id' => $payment->reference,
                'amount' => ['currency_code' => 'USD', 'value' => $amount],
                'payments' => ['captures' => [[
                    'id' => 'CAPTURE-CONTROLLER-0001',
                    'status' => 'COMPLETED',
                    'amount' => ['currency_code' => 'USD', 'value' => $amount],
                ]]],
            ]],
        ];
    }

    private function fakePayPal(Payment $payment, string $signatureStatus = 'SUCCESS', ?string $orderId = null): void
    {
        $orderId ??= self::ORDER_ID;

        Http::fake(function ($request) use ($payment, $signatureStatus, $orderId) {
            if (str_ends_with($request->url(), '/v1/oauth2/token')) {
                return Http::response(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer', 'expires_in' => 900], 200);
            }

            if (str_ends_with($request->url(), '/v1/notifications/verify-webhook-signature')) {
                return Http::response(['verification_status' => $signatureStatus], 200);
            }

            if (str_ends_with($request->url(), '/v2/checkout/orders/' . $orderId)) {
                return Http::response($this->order($payment, $orderId), 200);
            }

            return Http::response(['message' => 'Unexpected PayPal test request.'], 500);
        });
    }

    private function sendWebhook(array $event)
    {
        return $this->withHeaders([
            'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            'PAYPAL-CERT-URL' => self::BASE_URL . '/cert/test',
            'PAYPAL-TRANSMISSION-ID' => 'transmission-controller-test',
            'PAYPAL-TRANSMISSION-SIG' => 'signature-controller-test',
            'PAYPAL-TRANSMISSION-TIME' => '2026-11-13T10:00:00Z',
        ])->postJson(route('webhooks.paypal'), $event);
    }

    public function test_invalid_paypal_signature_is_rejected_without_changing_payment(): void
    {
        $payment = $this->payment(metadata: ['paypal_order_id' => self::ORDER_ID]);
        $this->fakePayPal($payment, 'FAILURE');

        $this->sendWebhook($this->event($payment))
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthorized.']);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(0, Subscription::where('user_id', $payment->user_id)->count());
    }

    public function test_authenticated_webhook_for_unknown_payment_reference_is_ignored(): void
    {
        $payment = $this->payment(metadata: ['paypal_order_id' => self::ORDER_ID]);
        $event = $this->event($payment);

        $this->fakePayPalOrderWithReference(
            $payment,
            'LQ-UNKNOWN-REFERENCE',
            self::ORDER_ID
        );

        $this->sendWebhook($event)
            ->assertOk()
            ->assertJson([
                'status' => 'ignored',
            ]);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);

        $this->assertSame(
            0,
            Subscription::where('user_id', $payment->user_id)->count()
        );
    }

    public function test_valid_completed_webhook_reconciles_payment_and_activates_subscription(): void
    {
        $payment = $this->payment(metadata: ['paypal_order_id' => self::ORDER_ID]);
        $this->fakePayPal($payment);

        $this->sendWebhook($this->event($payment))
            ->assertOk()
            ->assertJson(['status' => 'succeeded']);

        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
        $this->assertSame(1, Subscription::where('user_id', $payment->user_id)->where('status', 'active')->count());
    }

    public function test_repeated_delivery_does_not_activate_or_extend_subscription_twice(): void
    {
        $payment = $this->payment(metadata: ['paypal_order_id' => self::ORDER_ID]);
        $this->fakePayPal($payment);
        $event = $this->event($payment);

        $this->sendWebhook($event)->assertOk()->assertJson(['status' => 'succeeded']);
        $firstEndsAt = Subscription::where('user_id', $payment->user_id)->value('ends_at');

        $this->fakePayPal($payment);
        $this->sendWebhook($event)->assertOk()->assertJson(['status' => 'unchanged']);

        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
        $this->assertSame(1, Subscription::where('user_id', $payment->user_id)->count());
        $this->assertEquals($firstEndsAt, Subscription::where('user_id', $payment->user_id)->value('ends_at'));
    }

    public function test_webhook_with_order_id_different_from_stored_order_is_ignored(): void
    {
        $payment = $this->payment(metadata: ['paypal_order_id' => self::ORDER_ID]);
        $otherOrderId = '7A190127TN364715Q';
        $this->fakePayPal($payment, 'SUCCESS', $otherOrderId);
        $this->fakePayPalOrderWithReference($payment, $payment->reference, $otherOrderId);

        $this->sendWebhook($this->event($payment, [], $otherOrderId))
            ->assertOk()
            ->assertJson(['status' => 'ignored']);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(0, Subscription::where('user_id', $payment->user_id)->count());
    }

    public function test_paypal_webhook_cannot_settle_a_payment_owned_by_another_provider(): void
    {
        $payment = $this->payment(provider: 'paystack', metadata: ['paypal_order_id' => self::ORDER_ID]);
        $this->fakePayPal($payment);

        $this->sendWebhook($this->event($payment))
            ->assertOk()
            ->assertJson(['status' => 'ignored']);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(0, Subscription::where('user_id', $payment->user_id)->count());
    }

    private function fakePayPalOrderWithReference(Payment $payment, string $reference, string $orderId): void
    {
        $amount = intdiv($payment->amount_minor, 100) . '.' . str_pad((string) ($payment->amount_minor % 100), 2, '0', STR_PAD_LEFT);
        Http::fake(function ($request) use ($payment, $reference, $orderId, $amount) {
            if (str_ends_with($request->url(), '/v1/oauth2/token')) {
                return Http::response(['access_token' => 'sandbox-access-token', 'token_type' => 'Bearer', 'expires_in' => 900], 200);
            }
            if (str_ends_with($request->url(), '/v1/notifications/verify-webhook-signature')) {
                return Http::response(['verification_status' => 'SUCCESS'], 200);
            }
            if (str_ends_with($request->url(), '/v2/checkout/orders/' . $orderId)) {
                return Http::response([
                    'id' => $orderId,
                    'status' => 'COMPLETED',
                    'purchase_units' => [[
                        'custom_id' => $reference,
                        'amount' => ['currency_code' => 'USD', 'value' => $amount],
                        'payments' => ['captures' => [[
                            'id' => 'CAPTURE-CONTROLLER-0001',
                            'status' => 'COMPLETED',
                            'amount' => ['currency_code' => 'USD', 'value' => $amount],
                        ]]],
                    ]],
                ], 200);
            }
            return Http::response(['message' => 'Unexpected PayPal test request.'], 500);
        });
    }
}
