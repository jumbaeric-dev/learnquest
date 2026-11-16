<?php

namespace Tests\Feature\Billing;

use App\Livewire\ParentPortal\Billing\Index as BillingIndex;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\User;
use App\Services\Billing\BillingCheckoutService;
use App\Services\Billing\PaymentGatewayRegistry;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayPalCompletionTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://api-m.sandbox.paypal.com';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Role::findOrCreate('parent', 'web');
        $this->seed(PlanSeeder::class);

        config([
            'learnquest.gateways.default_provider' => 'paystack',
            'learnquest.gateways.currency_providers' => ['USD' => 'paypal'],
            'learnquest.gateways.providers.paypal.enabled' => true,
            'learnquest.gateways.providers.paypal.client_id' => 'client-id',
            'learnquest.gateways.providers.paypal.client_secret' => 'client-secret',
            'learnquest.gateways.providers.paypal.webhook_id' => 'WH-TEST',
            'learnquest.gateways.providers.paypal.base_url' => self::BASE,
        ]);
    }

    private function parentAccount(): User
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        return $parent;
    }

    private function usdMonthly(): PlanPrice
    {
        return PlanPrice::query()
            ->where('currency', 'USD')
            ->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))
            ->firstOrFail();
    }

    private function fakeOrderCreation(string $linkRel = 'approve'): void
    {
        Http::fake([
            self::BASE . '/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'token_type' => 'Bearer']),
            self::BASE . '/v2/checkout/orders' => Http::response([
                'id' => 'ORDER12345',
                'status' => 'CREATED',
                'links' => [['rel' => $linkRel, 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER12345']],
            ], 201),
        ]);
    }

    private function deliver(string $rawBody, array $headers = [])
    {
        $defaults = [
            'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            'PAYPAL-CERT-URL' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-1',
            'PAYPAL-TRANSMISSION-ID' => '69cd13f0-d67a-11e5-baa3-778b53f4ae55',
            'PAYPAL-TRANSMISSION-SIG' => 'c2lnbmF0dXJl',
            'PAYPAL-TRANSMISSION-TIME' => '2026-11-05T08:00:00Z',
        ];

        $server = ['CONTENT_TYPE' => 'application/json'];

        foreach (array_merge($defaults, $headers) as $name => $value) {
            $server['HTTP_' . str_replace('-', '_', $name)] = $value;
        }

        return $this->call('POST', route('webhooks.paypal'), [], [], [], $server, $rawBody);
    }

    public function test_usd_is_routed_to_paypal_and_other_currencies_use_the_default(): void
    {
        $registry = app(PaymentGatewayRegistry::class);

        $this->assertSame('paypal', $registry->providerForCurrency('USD'));
        $this->assertSame('paypal', $registry->providerForCurrency('usd'));
        $this->assertSame('paystack', $registry->providerForCurrency('KES'));
    }

    public function test_a_usd_checkout_goes_to_paypal(): void
    {
        $this->fakeOrderCreation();

        $result = app(BillingCheckoutService::class)->startCheckout(
            $this->parentAccount(),
            $this->usdMonthly()->id,
            (string) Str::uuid(),
        );

        $this->assertStringStartsWith('https://www.sandbox.paypal.com/', $result->checkoutUrl);
        $this->assertSame('paypal', Payment::query()->sole()->provider);
    }

    public function test_the_order_asks_for_no_shipping_address(): void
    {
        $this->fakeOrderCreation();

        app(BillingCheckoutService::class)->startCheckout(
            $this->parentAccount(),
            $this->usdMonthly()->id,
            (string) Str::uuid(),
        );

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v2/checkout/orders')
            && data_get($request->data(), 'application_context.shipping_preference') === 'NO_SHIPPING');
    }

    public function test_a_payer_action_approval_link_is_accepted(): void
    {
        $this->fakeOrderCreation('payer-action');

        $result = app(BillingCheckoutService::class)->startCheckout(
            $this->parentAccount(),
            $this->usdMonthly()->id,
            (string) Str::uuid(),
        );

        $this->assertStringContainsString('checkoutnow', $result->checkoutUrl);
    }

    public function test_webhook_verification_forwards_the_original_body_untouched(): void
    {
        Http::fake([
            self::BASE . '/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'token_type' => 'Bearer']),
            self::BASE . '/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'FAILURE']),
        ]);

        // Escaped slashes and an empty object would be changed by decode-then-encode.
        $raw = '{"id":"WH-RAW-1","event_type":"PAYMENT.CAPTURE.COMPLETED","summary":"See https:\/\/example.test\/x","resource":{"id":"CAP-1","links":[],"meta":{}}}';

        $this->deliver($raw)->assertUnauthorized();

        Http::assertSent(fn ($request) => str_contains($request->url(), 'verify-webhook-signature')
            && str_contains($request->body(), '"webhook_event":' . $raw));
    }

    public function test_an_untrusted_certificate_url_is_rejected_before_calling_paypal(): void
    {
        Http::fake();

        $raw = '{"id":"WH-CERT-1","event_type":"PAYMENT.CAPTURE.COMPLETED","resource":{"id":"CAP-1"}}';

        $this->deliver($raw, ['PAYPAL-CERT-URL' => 'https://evil.example/certs/1'])->assertUnauthorized();

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'verify-webhook-signature'));
    }

    public function test_the_billing_page_offers_paypal_for_usd(): void
    {
        Livewire::actingAs($this->parentAccount())
            ->test(BillingIndex::class)
            ->call('setCurrency', 'USD')
            ->assertSee('PayPal')
            ->assertSee('Choose Monthly');
    }
}