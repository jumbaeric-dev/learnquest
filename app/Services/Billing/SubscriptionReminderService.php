<?php

namespace App\Services\Billing;

use App\Models\Subscription;
use App\Models\SubscriptionReminder;
use App\Notifications\SubscriptionReminderNotification;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;

final class SubscriptionReminderService
{
    public function __construct(private readonly EntitlementService $entitlements)
    {
    }

    /** @return array{sent:int,skipped:int,failed:int} */
    public function sendDue(): array
    {
        $result = ['sent' => 0, 'skipped' => 0, 'failed' => 0];

        if (! $this->entitlements->enforced()) {
            return $result;
        }

        foreach ($this->candidates() as $subscription) {
            $kind = $this->kindFor($subscription);
            $parent = $subscription->user;

            if (
                $kind === null
                || ! $parent
                || ! $parent->isParent()
                || blank($parent->email)
                || $this->alreadySent($subscription, $kind)
                // Paying during a trial ends the trial row; the parent is not lapsing.
                || $this->hasAnotherCurrent($subscription)
            ) {
                $result['skipped']++;

                continue;
            }

            // Claim first so two runs can never send the same reminder twice.
            try {
                $reminder = SubscriptionReminder::create([
                    'subscription_id' => $subscription->id,
                    'kind' => $kind,
                    'period_ends_at' => $subscription->ends_at,
                    'sent_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                $result['skipped']++;

                continue;
            }

            try {
                $parent->notify(new SubscriptionReminderNotification($subscription, $kind));
                $result['sent']++;
            } catch (\Throwable $exception) {
                // Release the claim so the next run retries.
                $reminder->delete();
                report($exception);
                $result['failed']++;
            }
        }

        return $result;
    }

    /** @return Collection<int, Subscription> */
    private function candidates(): Collection
    {
        $config = (array) config('learnquest.billing.reminders', []);

        $maxBefore = max([
            0,
            ...(array) ($config['trial_days_before'] ?? []),
            ...(array) ($config['paid_days_before'] ?? []),
        ]);

        $expiredWindow = max(1, (int) ($config['expired_window_days'] ?? 3));

        return Subscription::query()
            ->with(['user', 'plan'])
            ->whereIn('status', [
                Subscription::STATUS_TRIALING,
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_CANCELED,
            ])
            ->where('ends_at', '<=', now()->addDays($maxBefore))
            ->where('ends_at', '>=', now()->subDays($expiredWindow))
            ->orderBy('id')
            ->get();
    }

    /**
     * The single most urgent reminder that applies today, or null.
     * A parent first seen late gets only the latest reminder, not a burst.
     */
    private function kindFor(Subscription $subscription): ?string
    {
        $config = (array) config('learnquest.billing.reminders', []);
        $now = now();

        if ($subscription->ends_at->lte($now)) {
            $window = max(1, (int) ($config['expired_window_days'] ?? 3));

            return $subscription->ends_at->gte($now->copy()->subDays($window)) ? 'expired' : null;
        }

        $days = (array) ($subscription->isTrial()
            ? ($config['trial_days_before'] ?? [])
            : ($config['paid_days_before'] ?? []));

        sort($days);

        $daysLeft = (int) ceil($now->diffInSeconds($subscription->ends_at, false) / 86400);

        foreach ($days as $threshold) {
            if ($daysLeft <= (int) $threshold) {
                return 'ending_' . (int) $threshold;
            }
        }

        return null;
    }

    private function alreadySent(Subscription $subscription, string $kind): bool
    {
        return SubscriptionReminder::query()
            ->where('subscription_id', $subscription->id)
            ->where('kind', $kind)
            ->where('period_ends_at', $subscription->ends_at)
            ->exists();
    }

    private function hasAnotherCurrent(Subscription $subscription): bool
    {
        return Subscription::query()
            ->current()
            ->where('user_id', $subscription->user_id)
            ->where('id', '!=', $subscription->id)
            ->exists();
    }
}