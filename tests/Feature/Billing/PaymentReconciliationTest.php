<?php

namespace Tests\Feature\Billing;

use App\Livewire\ParentPortal\Billing\Index as BillingIndex;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Billing\PaymentReconciliationService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_URL = 'https://api.intasend.com/api/v1/payment/status/';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Role::findOrCreate('parent', 'web');
        $this->seed(PlanSeeder::class);

        config([
            'learnquest.gateways.default_provider' => 'intasend',
            'learnquest.gateways.providers.intasend.enabled' => true,
            'learnquest.gateways.providers.intasend.secret_key' => 'ISSecretKey_test_example',
            'learnquest.gateways.providers.intasend.public_key' => 'ISPubKey_test_example',
            'learnquest.gateways.providers.intasend.webhook_secret' => 'learnquest-test-challenge',
            'learnquest.gateways.providers.intasend.base_url' => 'https://api.intasend.com',
        ]);
    }

    private function payment(string $reference = 'LQ-RECON-TEST-0001', array $overrides = []): Payment
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        $price = PlanPrice::query()->where('currency', 'KES')->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))->firstOrFail();

        return Payment::create(array_merge([
            'user_id' => $parent->id,
            'plan_id' => $price->plan_id,
            'plan_price_id' => $price->id,
            'plan_key' => 'premium',
            'duration_months' => 1,
            'reference' => $reference,
            'provider' => 'intasend',
            'amount_minor' => $price->amount_minor,
            'currency' => 'KES',
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addMinutes(30),
            'metadata' => ['intasend_invoice_id' => 'INV-1'],
        ], $overrides));
    }

    private function statusBody(Payment $payment, string $state = 'COMPLETE', string $value = '499.00'): array
    {
        return ['invoice' => [
            'invoice_id' => 'INV-1', 'api_ref' => $payment->reference, 'state' => $state,
            'value' => $value, 'currency' => 'KES', 'provider_ref' => 'MPESA-1',
        ]];
    }

    private function webhook(Payment $payment, array $overrides = [])
    {
        // No "topic" key on purpose: the receiver must not depend on it.
        return $this->postJson(route('webhooks.intasend'), array_merge([
            'challenge' => 'learnquest-test-challenge',
            'invoice_id' => 'INV-1', 'api_ref' => $payment->reference,
            'state' => 'COMPLETE', 'value' => '499.00', 'currency' => 'KES', 'provider' => 'M-PESA',
        ], $overrides));
    }

    private function hasActiveSubscription(Payment $payment): bool
    {
        return Subscription::where('user_id', $payment->user_id)->where('status', 'active')->exists();
    }

    public function test_reconcile_settles_a_payment_the_provider_reports_complete(): void
    {
        $payment = $this->payment();
        Http::fake([self::STATUS_URL => Http::response($this->statusBody($payment))]);

        $result = app(PaymentReconciliationService::class)->reconcile($payment);

        $this->assertSame('succeeded', $result['status']);
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
        $this->assertTrue($this->hasActiveSubscription($payment));
    }

    public function test_reconcile_leaves_an_in_progress_payment_unsettled(): void
    {
        $payment = $this->payment();
        Http::fake([self::STATUS_URL => Http::response($this->statusBody($payment, 'PROCESSING'))]);

        $result = app(PaymentReconciliationService::class)->reconcile($payment);

        $this->assertSame('pending', $result['status']);
        $this->assertSame(Payment::STATUS_PROCESSING, $payment->fresh()->status);
        $this->assertFalse($this->hasActiveSubscription($payment));
    }

    public function test_reconcile_skips_a_payment_that_never_got_an_invoice(): void
    {
        $payment = $this->payment('LQ-RECON-NOINV', ['metadata' => null]);
        Http::fake();

        $result = app(PaymentReconciliationService::class)->reconcile($payment);

        $this->assertSame('no_invoice', $result['status']);
        Http::assertNothingSent();
    }

    public function test_reconciling_twice_grants_access_once(): void
    {
        $payment = $this->payment();
        Http::fake([self::STATUS_URL => Http::response($this->statusBody($payment))]);

        $service = app(PaymentReconciliationService::class);
        $service->reconcile($payment);
        $endsAt = Subscription::where('user_id', $payment->user_id)->value('ends_at');

        $second = $service->reconcile($payment->fresh());

        $this->assertSame('unchanged', $second['status']);
        $this->assertSame(1, Subscription::where('user_id', $payment->user_id)->count());
        $this->assertEquals($endsAt, Subscription::where('user_id', $payment->user_id)->value('ends_at'));
    }

    public function test_webhook_for_an_unknown_reference_is_acknowledged_not_rejected(): void
    {
        $payment = $this->payment();

        $this->webhook($payment, ['api_ref' => 'SOMEONE-ELSES-PAYMENT'])
            ->assertOk()
            ->assertJson(['status' => 'ignored']);
    }

    public function test_webhook_with_a_wrong_challenge_is_unauthorized(): void
    {
        $payment = $this->payment();

        $this->webhook($payment, ['challenge' => 'wrong'])->assertUnauthorized();
    }

    public function test_webhook_that_lies_about_the_amount_still_settles_from_the_provider_status(): void
    {
        $payment = $this->payment();
        Http::fake([self::STATUS_URL => Http::response($this->statusBody($payment))]);

        $this->webhook($payment, ['value' => '1.00'])
            ->assertOk()
            ->assertJson(['status' => 'succeeded']);

        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
    }

    public function test_forged_complete_webhook_grants_nothing_while_the_provider_says_pending(): void
    {
        $payment = $this->payment();
        Http::fake([self::STATUS_URL => Http::response($this->statusBody($payment, 'PENDING'))]);

        $this->webhook($payment)->assertOk();

        $this->assertNotSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
        $this->assertFalse($this->hasActiveSubscription($payment));
    }

    public function test_status_api_outage_is_acknowledged_and_settled_by_the_scheduled_sweep(): void
    {
        $payment = $this->payment();
        Http::fake([self::STATUS_URL => Http::sequence()
            ->push(['detail' => 'down'], 500)
            ->push($this->statusBody($payment))]);

        $this->webhook($payment)->assertOk()->assertJson(['status' => 'accepted']);
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);

        $this->artisan('learnquest:reconcile-payments')->assertSuccessful();

        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
        $this->assertTrue($this->hasActiveSubscription($payment));
    }

    public function test_sweep_closes_attempts_abandoned_for_over_a_day(): void
    {
        $payment = $this->payment('LQ-RECON-OLD', [
            'metadata' => null,
            'expires_at' => now()->subDays(2),
        ]);

        $this->artisan('learnquest:reconcile-payments')->assertSuccessful();

        $this->assertSame(Payment::STATUS_CANCELLED, $payment->fresh()->status);
        $this->assertSame('abandoned', $payment->fresh()->failure_code);
    }

    public function test_billing_page_settles_a_returning_parents_payment(): void
    {
        $payment = $this->payment();
        Http::fake([self::STATUS_URL => Http::response($this->statusBody($payment))]);

        Livewire::actingAs($payment->user)
            ->test(BillingIndex::class)
            ->assertSee('Payment received');

        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
    }

    public function test_switching_billing_period_uses_a_fresh_checkout_attempt(): void
    {
        Http::fake(['https://api.intasend.com/api/v1/checkout/' => Http::sequence()
            ->push(['id' => 'INV-A', 'url' => 'https://checkout.intasend.com/a'])
            ->push(['id' => 'INV-B', 'url' => 'https://checkout.intasend.com/b'])]);

        $parent = User::factory()->create();
        $parent->assignRole('parent');

        $prices = PlanPrice::query()->where('currency', 'KES')
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))
            ->orderBy('months')->take(2)->get();

        Livewire::actingAs($parent)
            ->test(BillingIndex::class)
            ->call('startCheckout', $prices[0]->id)
            ->call('startCheckout', $prices[1]->id)
            ->assertHasNoErrors();

        $this->assertSame(2, Payment::where('user_id', $parent->id)->count());
    }
}