<?php

namespace Tests\Feature\Billing;

use App\Livewire\ParentPortal\Billing\Index as BillingIndex;
use App\Livewire\ParentPortal\Dashboard\Index as DashboardIndex;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionReminderNotification;
use App\Services\Billing\EntitlementService;
use App\Services\Billing\SubscriptionService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class GracePeriodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Carbon::setTestNow(Carbon::parse('2026-11-10 08:00:00'));

        $this->seed(RolesSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(PlanSeeder::class);

        config([
            'learnquest.billing.enforced' => true,
            'learnquest.billing.free_plan_enabled' => true,
            'learnquest.billing.grace_days' => 2,
            'learnquest.gateways.default_provider' => 'intasend',
            'learnquest.gateways.providers.intasend.enabled' => true,
            'learnquest.gateways.providers.intasend.driver' =>
                \App\Services\Billing\Gateways\IntaSendGateway::class,
            'learnquest.gateways.providers.intasend.currencies' => ['KES'],
            'learnquest.gateways.providers.intasend.public_key' => 'test-public-key',
            'learnquest.gateways.providers.intasend.secret_key' => 'test-secret-key',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function subscribe(User $parent, string $status, $endsAt, ?string $gateway = 'paystack'): Subscription
    {
        return Subscription::create([
            'user_id' => $parent->id,
            'plan_id' => Plan::where('key', 'premium')->value('id'),
            'status' => $status,
            'starts_at' => now()->subDays(40),
            'ends_at' => $endsAt,
            'auto_renews' => false,
            'gateway' => $gateway,
        ]);
    }

    private function monthlyPrice(): PlanPrice
    {
        return PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))
            ->firstOrFail();
    }

    public function test_a_lapsed_paid_plan_keeps_working_during_grace(): void
    {
        $parent = $this->userWithRole('parent');
        $this->subscribe($parent, 'active', now()->subDay());

        $entitlements = app(EntitlementService::class);
        $summary = $entitlements->summary($parent);

        $this->assertSame('premium', $entitlements->planKeyFor($parent));
        $this->assertSame(4, $entitlements->limit($parent, 'children'));
        $this->assertSame('grace', $summary['status']);
        $this->assertTrue($summary['grace_ends_at']->equalTo(now()->addDay()));
        $this->assertNull($summary['days_left']);
    }

    public function test_access_ends_when_grace_runs_out(): void
    {
        $parent = $this->userWithRole('parent');
        $this->subscribe($parent, 'active', now()->subDays(3));

        $entitlements = app(EntitlementService::class);

        $this->assertSame('free', $entitlements->planKeyFor($parent));
        $this->assertSame('free', $entitlements->summary($parent)['status']);
    }

    public function test_trials_and_complimentary_time_get_no_grace(): void
    {
        $trialParent = $this->userWithRole('parent');
        $this->subscribe($trialParent, 'trialing', now()->subDay(), null);

        $giftedParent = $this->userWithRole('parent');
        $this->subscribe($giftedParent, 'active', now()->subDay(), 'complimentary');

        $entitlements = app(EntitlementService::class);

        $this->assertSame('free', $entitlements->planKeyFor($trialParent));
        $this->assertSame('free', $entitlements->planKeyFor($giftedParent));
    }

    public function test_grace_can_be_switched_off(): void
    {
        config(['learnquest.billing.grace_days' => 0]);

        $parent = $this->userWithRole('parent');
        $this->subscribe($parent, 'active', now()->subHour());

        $this->assertSame('free', app(EntitlementService::class)->planKeyFor($parent));
    }

    public function test_paying_during_grace_continues_from_the_original_end_date(): void
    {
        $parent = $this->userWithRole('parent');
        $endedAt = now()->subDay();
        $subscription = $this->subscribe($parent, 'active', $endedAt);

        $renewed = app(SubscriptionService::class)->activate($parent, $this->monthlyPrice(), 'paystack', 'LQ-GRACE-0001');

        $this->assertSame($subscription->id, $renewed->id);
        $this->assertSame(1, Subscription::where('user_id', $parent->id)->count());
        $this->assertTrue($renewed->ends_at->equalTo($endedAt->copy()->addMonthsNoOverflow(1)));
        $this->assertSame('active', app(EntitlementService::class)->summary($parent)['status']);
    }

    public function test_paying_after_grace_starts_a_fresh_term_from_now(): void
    {
        $parent = $this->userWithRole('parent');
        $this->subscribe($parent, 'active', now()->subDays(5));

        $renewed = app(SubscriptionService::class)->activate($parent, $this->monthlyPrice(), 'paystack', 'LQ-GRACE-0002');

        $this->assertSame(2, Subscription::where('user_id', $parent->id)->count());
        $this->assertTrue($renewed->ends_at->equalTo(now()->addMonthNoOverflow()));
    }

    public function test_an_admin_grant_during_grace_continues_from_the_original_end_date(): void
    {
        $parent = $this->userWithRole('parent');
        $endedAt = now()->subDay();
        $subscription = $this->subscribe($parent, 'active', $endedAt);

        $grant = app(SubscriptionService::class)->grantComplimentary(
            $parent,
            Plan::where('key', 'premium')->firstOrFail(),
            10,
            'Goodwill after a delayed M-Pesa confirmation',
            $this->userWithRole('admin'),
        );

        $this->assertSame($subscription->id, $grant->subscription_id);
        $this->assertTrue($grant->new_ends_at->equalTo($endedAt->copy()->addDays(10)));
        $this->assertTrue($grant->previous_ends_at->equalTo($endedAt));
    }

    public function test_the_dashboard_banner_explains_grace(): void
    {
        $parent = $this->userWithRole('parent');
        $this->subscribe($parent, 'active', now()->subDay());

        Livewire::actingAs($parent)
            ->test(DashboardIndex::class)
            ->assertSee('access continues until');
    }

    public function test_the_billing_page_explains_grace_and_offers_renewal(): void
    {
        $parent = $this->userWithRole('parent');
        $this->subscribe($parent, 'active', now()->subDay());

        Livewire::actingAs($parent)
            ->test(BillingIndex::class)
            ->assertSee('Your access continues until')
            ->assertSee('Add Monthly')
            ->assertSee('continues from the day your plan ended');
    }

    public function test_the_ended_email_explains_grace_only_while_it_applies(): void
    {
        $parent = $this->userWithRole('parent');

        $inGrace = $this->subscribe($parent, 'active', now()->subDay())->load('plan');
        $mail = (new SubscriptionReminderNotification($inGrace, 'expired'))->toMail($parent);

        $this->assertStringContainsString('keeping your children’s access on', implode(' ', $mail->introLines));

        $pastGrace = $this->subscribe($this->userWithRole('parent'), 'active', now()->subDays(3))->load('plan');
        $mail = (new SubscriptionReminderNotification($pastGrace, 'expired'))->toMail($parent);

        $this->assertStringContainsString('progress is safe', implode(' ', $mail->introLines));
    }

    public function test_the_admin_state_label_shows_grace(): void
    {
        $parent = $this->userWithRole('parent');
        $subscription = $this->subscribe($parent, 'active', now()->subDay());

        $this->assertSame('Grace period', $subscription->stateLabel());

        Carbon::setTestNow(now()->addDays(3));

        $this->assertSame('Expired', $subscription->fresh()->stateLabel());
    }
}