<?php

namespace Tests\Feature\Billing;

use App\Exceptions\ContentLockedException;
use App\Exceptions\PlanLimitReachedException;
use App\Models\Child;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Billing\EntitlementService;
use App\Services\Billing\SubscriptionService;
use App\Services\Nova\NovaConversationService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EntitlementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-03 12:00:00'));

        Role::findOrCreate('parent', 'web');
        $this->seed(PlanSeeder::class);

        config(['learnquest.billing.enforced' => true]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function parentAccount(): User
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        return $parent;
    }

    private function subscribe(User $parent, string $status, $startsAt, $endsAt): Subscription
    {
        $subscription = Subscription::create([
            'user_id' => $parent->id,
            'plan_id' => Plan::where('key', 'premium')->value('id'),
            'status' => $status,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        app(EntitlementService::class)->forget($parent);

        return $subscription;
    }

    private function premiumPrice(string $currency = 'KES', int $months = 1): PlanPrice
    {
        return PlanPrice::query()
            ->whereHas('plan', fn ($q) => $q->where('key', 'premium'))
            ->where('currency', $currency)
            ->where('months', $months)
            ->firstOrFail();
    }

    public function test_nobody_is_restricted_while_billing_is_not_enforced(): void
    {
        config(['learnquest.billing.enforced' => false]);

        $parent = $this->parentAccount();
        $entitlements = app(EntitlementService::class);

        $this->assertNull($entitlements->limit($parent, 'children'));
        $this->assertTrue($entitlements->allows($parent, 'skill_reports'));
        $this->assertTrue($entitlements->canAddChild($parent));
    }

    public function test_a_parent_without_a_subscription_is_on_the_free_plan(): void
    {
        $parent = $this->parentAccount();
        $entitlements = app(EntitlementService::class);

        $this->assertSame('free', $entitlements->planKeyFor($parent));
        $this->assertSame(1, $entitlements->limit($parent, 'children'));
        $this->assertFalse($entitlements->allows($parent, 'skill_reports'));
        $this->assertTrue($entitlements->canAddChild($parent));

        Child::factory()->create(['parent_id' => $parent->id]);

        $this->assertFalse($entitlements->canAddChild($parent));

        $this->expectException(PlanLimitReachedException::class);
        $entitlements->assertCanAddChild($parent);
    }

    public function test_an_active_trial_grants_premium(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'trialing', now()->subDay(), now()->addDays(13));

        $entitlements = app(EntitlementService::class);

        $this->assertSame('premium', $entitlements->planKeyFor($parent));
        $this->assertSame(4, $entitlements->limit($parent, 'children'));
        $this->assertTrue($entitlements->allows($parent, 'skill_reports'));
    }

    public function test_an_expired_subscription_falls_back_to_free(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'active', now()->subMonths(2), now()->subDay());

        $this->assertSame('free', app(EntitlementService::class)->planKeyFor($parent));
    }

    public function test_a_canceled_subscription_keeps_access_until_it_ends(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'canceled', now()->subDays(10), now()->addDays(5));

        $this->assertSame('premium', app(EntitlementService::class)->planKeyFor($parent));
    }

    public function test_trial_starts_once_for_enforced_parents_only(): void
    {
        $subscriptions = app(SubscriptionService::class);
        $parent = $this->parentAccount();

        $trial = $subscriptions->startTrialIfEligible($parent);

        $this->assertNotNull($trial);
        $this->assertTrue($trial->isTrial());
        $this->assertTrue($trial->ends_at->equalTo(now()->addDays(14)));
        $this->assertNull($subscriptions->startTrialIfEligible($parent));

        $notAParent = User::factory()->create();
        $this->assertNull($subscriptions->startTrialIfEligible($notAParent));

        config(['learnquest.billing.enforced' => false]);
        $this->assertNull($subscriptions->startTrialIfEligible($this->parentAccount()));
    }

    public function test_activating_a_price_grants_access_and_renewal_extends_it(): void
    {
        $subscriptions = app(SubscriptionService::class);
        $parent = $this->parentAccount();

        $first = $subscriptions->activate($parent, $this->premiumPrice('KES', 1), 'mpesa', 'REF1');

        $this->assertSame('active', $first->status);
        $this->assertTrue($first->ends_at->equalTo(now()->addMonthNoOverflow()));
        $this->assertSame('premium', app(EntitlementService::class)->planKeyFor($parent));

        $renewed = $subscriptions->activate($parent, $this->premiumPrice('KES', 3), 'mpesa', 'REF2');

        $this->assertSame($first->id, $renewed->id);
        $this->assertTrue($renewed->ends_at->equalTo(now()->addMonthsNoOverflow(4)));
        $this->assertSame(1, Subscription::where('user_id', $parent->id)->count());
    }

    public function test_paying_during_a_trial_ends_the_trial_and_starts_paid_access(): void
    {
        $subscriptions = app(SubscriptionService::class);
        $parent = $this->parentAccount();

        $trial = $subscriptions->startTrialIfEligible($parent);
        $paid = $subscriptions->activate($parent, $this->premiumPrice('USD', 12), 'paypal', 'PAY1', true);

        $this->assertNotSame($trial->id, $paid->id);
        $this->assertTrue($trial->fresh()->ends_at->lessThanOrEqualTo(now()));
        $this->assertSame('active', $paid->status);
        $this->assertTrue($paid->auto_renews);
        $this->assertTrue($paid->ends_at->equalTo(now()->addYear()));
    }

    /** @return array{0: Lesson, 1: Lesson} first-module and second-module lessons */
    private function lessonsInTwoModules(): array
    {
        $firstLesson = LessonActivity::factory()->create()->lesson;
        $firstModule = $firstLesson->module;

        $secondModule = CourseModule::create([
            'course_id' => $firstModule->course_id,
            'title' => 'Second module',
            'position' => $firstModule->position + 1,
        ]);

        $secondLesson = Lesson::create([
            'learning_module_id' => $secondModule->id,
            'title' => 'Second lesson',
            'position' => 1,
            'xp_reward' => 10,
            'estimated_minutes' => 5,
            'is_published' => true,
        ]);

        return [$firstLesson, $secondLesson];
    }

    public function test_free_plan_includes_only_the_first_module_of_each_course(): void
    {
        [$first, $second] = $this->lessonsInTwoModules();

        $parent = $this->parentAccount();
        $child = Child::factory()->create(['parent_id' => $parent->id]);
        $entitlements = app(EntitlementService::class);

        $this->assertTrue($entitlements->isLessonIncluded($child, $first));
        $this->assertFalse($entitlements->isLessonIncluded($child, $second));

        $this->expectException(ContentLockedException::class);
        $entitlements->assertLessonIncluded($child, $second);
    }

    public function test_premium_unrestricted_and_parentless_children_see_every_lesson(): void
    {
        [, $second] = $this->lessonsInTwoModules();
        $entitlements = app(EntitlementService::class);

        $premiumParent = $this->parentAccount();
        $this->subscribe($premiumParent, 'active', now()->subDay(), now()->addMonth());
        $premiumChild = Child::factory()->create(['parent_id' => $premiumParent->id]);

        $this->assertTrue($entitlements->isLessonIncluded($premiumChild, $second));

        $orphan = Child::factory()->create(['parent_id' => null]);
        $this->assertTrue($entitlements->isLessonIncluded($orphan, $second));
    }

    public function test_free_plan_caps_nova_messages_per_day(): void
    {
        $parent = $this->parentAccount();
        $child = Child::factory()->create(['parent_id' => $parent->id]);
        $nova = app(NovaConversationService::class);

        for ($i = 0; $i < 4; $i++) {
            $child->novaMessages()->create(['role' => 'user', 'content' => "Hello {$i}"]);
        }

        $this->assertFalse($nova->hasReachedDailyLimit($child));

        $child->novaMessages()->create(['role' => 'user', 'content' => 'Hello 5']);

        $this->assertTrue($nova->hasReachedDailyLimit($child));
    }
}