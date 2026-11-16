<?php

namespace Tests\Feature\Billing;

use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\SubscriptionReminder;
use App\Models\User;
use App\Notifications\SubscriptionReminderNotification;
use App\Services\Billing\SubscriptionReminderService;
use App\Services\Billing\SubscriptionService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-10 08:00:00'));

        Role::findOrCreate('parent', 'web');
        $this->seed(PlanSeeder::class);

        config(['learnquest.billing.enforced' => true]);

        Notification::fake();
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

    private function subscribe(User $parent, string $status, $endsAt): Subscription
    {
        return Subscription::create([
            'user_id' => $parent->id,
            'plan_id' => Plan::where('key', 'premium')->value('id'),
            'status' => $status,
            'starts_at' => now()->subDays(20),
            'ends_at' => $endsAt,
            'auto_renews' => false,
        ]);
    }

    private function sendDue(): array
    {
        return app(SubscriptionReminderService::class)->sendDue();
    }

    private function assertKindSent(User $parent, string $kind): void
    {
        Notification::assertSentTo(
            $parent,
            SubscriptionReminderNotification::class,
            fn ($notification) => $notification->kind === $kind
        );
    }

    public function test_a_trial_ending_soon_gets_one_reminder_even_if_the_command_runs_twice(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'trialing', now()->addDays(2));

        $this->sendDue();
        $this->sendDue();

        $this->assertKindSent($parent, 'ending_3');
        Notification::assertSentToTimes($parent, SubscriptionReminderNotification::class, 1);
        $this->assertSame(1, SubscriptionReminder::count());
    }

    public function test_nothing_is_sent_while_there_is_plenty_of_time_left(): void
    {
        $this->subscribe($this->parentAccount(), 'trialing', now()->addDays(10));
        $this->subscribe($this->parentAccount(), 'active', now()->addDays(20));

        $this->sendDue();

        Notification::assertNothingSent();
    }

    public function test_paid_plans_are_reminded_at_seven_three_and_one_days(): void
    {
        $weekOut = $this->parentAccount();
        $threeOut = $this->parentAccount();
        $lastDay = $this->parentAccount();

        $this->subscribe($weekOut, 'active', now()->addDays(6));
        $this->subscribe($threeOut, 'active', now()->addDays(3)->subHour());
        $this->subscribe($lastDay, 'active', now()->addHours(10));

        $this->sendDue();

        $this->assertKindSent($weekOut, 'ending_7');
        $this->assertKindSent($threeOut, 'ending_3');
        $this->assertKindSent($lastDay, 'ending_1');
    }

    public function test_a_parent_seen_late_gets_only_the_most_urgent_reminder(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'active', now()->addDays(2));

        $this->sendDue();

        Notification::assertSentToTimes($parent, SubscriptionReminderNotification::class, 1);
        $this->assertKindSent($parent, 'ending_3');
    }

    public function test_renewing_re_arms_reminders_for_the_new_period(): void
    {
        $parent = $this->parentAccount();
        $subscription = $this->subscribe($parent, 'active', now()->addDays(2));

        $this->sendDue();

        $price = PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))
            ->firstOrFail();

        app(SubscriptionService::class)->activate($parent, $price, 'paystack', 'LQ-REARM-0001');

        $subscription->refresh();

        Carbon::setTestNow($subscription->ends_at->copy()->subDays(2));

        $this->sendDue();

        Notification::assertSentToTimes($parent, SubscriptionReminderNotification::class, 2);
    }

    public function test_an_expired_plan_gets_one_expired_notice_inside_the_window_only(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'active', now()->subDay());

        $this->sendDue();
        $this->assertKindSent($parent, 'expired');

        Carbon::setTestNow(now()->addDays(5));
        $this->sendDue();

        Notification::assertSentToTimes($parent, SubscriptionReminderNotification::class, 1);
    }

    public function test_paying_during_a_trial_does_not_trigger_a_trial_ended_notice(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'trialing', now()->addDays(5));

        $price = PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))
            ->firstOrFail();

        app(SubscriptionService::class)->activate($parent, $price, 'paystack', 'LQ-CONVERT-0001');

        Carbon::setTestNow(now()->addHour());

        $this->sendDue();

        Notification::assertNothingSent();
    }

    public function test_no_reminders_are_sent_before_billing_launches(): void
    {
        config(['learnquest.billing.enforced' => false]);

        $this->subscribe($this->parentAccount(), 'trialing', now()->addDays(2));

        $result = $this->sendDue();

        $this->assertSame(['sent' => 0, 'skipped' => 0, 'failed' => 0], $result);
        Notification::assertNothingSent();
    }

    public function test_the_command_reports_what_it_did(): void
    {
        $this->subscribe($this->parentAccount(), 'trialing', now()->addDays(2));

        $this->artisan('learnquest:send-subscription-reminders')
            ->expectsOutputToContain('Sent 1')
            ->assertSuccessful();
    }
}