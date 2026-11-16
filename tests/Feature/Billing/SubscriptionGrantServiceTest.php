<?php

namespace Tests\Feature\Billing;

use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Billing\EntitlementService;
use App\Services\Billing\SubscriptionService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubscriptionGrantServiceTest extends TestCase
{
    use RefreshDatabase;

    private const REASON = 'Late M-Pesa confirmation, customer emailed support';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-10 08:00:00'));

        $this->seed(RolesSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(PlanSeeder::class);

        config(['learnquest.billing.enforced' => true]);
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

    private function premium(): Plan
    {
        return Plan::where('key', 'premium')->firstOrFail();
    }

    private function subscribe(User $parent, string $status, $endsAt, ?string $gateway = 'paystack'): Subscription
    {
        return Subscription::create([
            'user_id' => $parent->id,
            'plan_id' => $this->premium()->id,
            'status' => $status,
            'starts_at' => now()->subDays(20),
            'ends_at' => $endsAt,
            'auto_renews' => false,
            'gateway' => $gateway,
        ]);
    }

    private function grant(User $parent, int $days = 30, ?User $admin = null, ?Plan $plan = null)
    {
        return app(SubscriptionService::class)->grantComplimentary(
            $parent,
            $plan ?? $this->premium(),
            $days,
            self::REASON,
            $admin ?? $this->userWithRole('admin'),
        );
    }

    public function test_a_grant_creates_a_complimentary_subscription_and_an_audit_record(): void
    {
        $parent = $this->userWithRole('parent');
        $admin = $this->userWithRole('admin');

        $grant = $this->grant($parent, 30, $admin);

        $subscription = $grant->subscription;

        $this->assertSame('active', $subscription->status);
        $this->assertSame('complimentary', $subscription->gateway);
        $this->assertTrue($subscription->ends_at->equalTo(now()->addDays(30)));

        $this->assertSame($admin->id, $grant->granted_by);
        $this->assertSame($parent->id, $grant->user_id);
        $this->assertSame(30, $grant->days);
        $this->assertNull($grant->previous_ends_at);
        $this->assertSame(self::REASON, $grant->reason);

        $this->assertSame('premium', app(EntitlementService::class)->planKeyFor($parent));
    }

    public function test_a_grant_stacks_onto_current_paid_access(): void
    {
        $parent = $this->userWithRole('parent');
        $subscription = $this->subscribe($parent, 'active', now()->addDays(10));

        $grant = $this->grant($parent, 20);

        $this->assertSame(1, Subscription::where('user_id', $parent->id)->count());
        $this->assertSame($subscription->id, $grant->subscription_id);
        $this->assertTrue($grant->previous_ends_at->equalTo(now()->addDays(10)));
        $this->assertTrue($subscription->fresh()->ends_at->equalTo(now()->addDays(30)));
    }

    public function test_a_grant_after_expiry_starts_from_now(): void
    {
        $parent = $this->userWithRole('parent');
        $this->subscribe($parent, 'active', now()->subDays(3));

        $grant = $this->grant($parent, 15);

        $this->assertTrue($grant->new_ends_at->equalTo(now()->addDays(15)));
        $this->assertSame(2, Subscription::where('user_id', $parent->id)->count());
    }

    public function test_a_grant_during_a_trial_keeps_the_remaining_trial_days(): void
    {
        $parent = $this->userWithRole('parent');
        $trial = $this->subscribe($parent, 'trialing', now()->addDays(5), null);

        $grant = $this->grant($parent, 30);

        $this->assertTrue($grant->new_ends_at->equalTo(now()->addDays(35)));
        $this->assertTrue($trial->fresh()->ends_at->lessThanOrEqualTo(now()));
        $this->assertSame('premium', app(EntitlementService::class)->planKeyFor($parent));
    }

    public function test_paying_later_stacks_on_top_of_complimentary_time(): void
    {
        $parent = $this->userWithRole('parent');
        $grant = $this->grant($parent, 30);

        $price = PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))
            ->firstOrFail();

        app(SubscriptionService::class)->activate($parent, $price, 'paystack', 'LQ-STACK-0001');

        $this->assertTrue(
            $grant->subscription->fresh()->ends_at->equalTo($grant->new_ends_at->copy()->addMonthsNoOverflow(1))
        );
    }

    public function test_only_authorised_admins_can_grant(): void
    {
        $this->expectException(AuthorizationException::class);

        $this->grant($this->userWithRole('parent'), 30, $this->userWithRole('school_admin'));
    }

    public function test_invalid_grants_are_rejected(): void
    {
        $parent = $this->userWithRole('parent');
        $admin = $this->userWithRole('admin');
        $service = app(SubscriptionService::class);

        $cases = [
            'days' => fn () => $service->grantComplimentary($parent, $this->premium(), 0, self::REASON, $admin),
            'days_max' => fn () => $service->grantComplimentary($parent, $this->premium(), 366, self::REASON, $admin),
            'reason' => fn () => $service->grantComplimentary($parent, $this->premium(), 30, 'short', $admin),
            'free plan' => fn () => $service->grantComplimentary($parent, Plan::where('key', 'free')->firstOrFail(), 30, self::REASON, $admin),
            'not a parent' => fn () => $service->grantComplimentary($this->userWithRole('admin'), $this->premium(), 30, self::REASON, $admin),
        ];

        foreach ($cases as $name => $attempt) {
            try {
                $attempt();
                $this->fail("Expected a ValidationException for: {$name}");
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }

        $this->assertSame(0, Subscription::count());
    }
}