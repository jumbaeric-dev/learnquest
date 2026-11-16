<?php

namespace App\Services\Billing;

use App\Models\Subscription;
use App\Models\User;

final class RenewalNoticeService
{
    public function __construct(private readonly EntitlementService $entitlements) {}

    /** @return array{level:string,message:string,cta:string}|null */
    public function forParent(User $parent): ?array
    {
        if (! $this->entitlements->enforced() || ! $parent->isParent()) {
            return null;
        }

        $config = (array) config('learnquest.billing.reminders.banner', []);
        $timezone = config('learnquest.daily_challenges.timezone') ?: config('app.timezone');

        $current = $this->entitlements->currentSubscription($parent);

        if ($current) {
            if ($current->inGrace()) {
                $graceEnds = $current->graceEndsAt()->copy()->setTimezone($timezone)->format('M j');
                $name = $current->plan?->name ?? 'plan';

                return [
                    'level' => 'danger',
                    'message' => "Your {$name} has ended, but your access continues until {$graceEnds}. Add time now so nothing is interrupted.",
                    'cta' => 'Add time',
                ];
            }
            
            $daysLeft = max(1, (int) ceil(now()->diffInSeconds($current->ends_at, false) / 86400));
            $threshold = $current->isTrial()
                ? (int) ($config['trial_days'] ?? 5)
                : (int) ($config['paid_days'] ?? 7);

            if ($daysLeft > $threshold) {
                return null;
            }

            $when = $daysLeft <= 1 ? 'within a day' : "in {$daysLeft} days";

            if ($current->isTrial()) {
                return [
                    'level' => 'warning',
                    'message' => "Your free trial ends {$when}. Choose a plan to keep everything unlocked.",
                    'cta' => 'Choose a plan',
                ];
            }

            $name = $current->plan?->name ?? 'plan';

            return [
                'level' => 'warning',
                'message' => "Your {$name} ends {$when}. Add more time to keep your children’s access.",
                'cta' => 'Add time',
            ];
        }

        $recent = Subscription::query()
            ->with('plan')
            ->where('user_id', $parent->id)
            ->whereIn('status', [
                Subscription::STATUS_TRIALING,
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_CANCELED,
            ])
            ->where('ends_at', '<=', now())
            ->where('ends_at', '>=', now()->subDays((int) ($config['expired_days'] ?? 14)))
            ->orderByDesc('ends_at')
            ->first();

        if (! $recent) {
            return null;
        }

        $label = $recent->isTrial() ? 'free trial' : ($recent->plan?->name ?? 'plan');
        $endedOn = $recent->ends_at->copy()->setTimezone($timezone)->format('M j, Y');

        return [
            'level' => 'danger',
            'message' => "Your {$label} ended on {$endedOn}. Choose a plan to unlock everything again.",
            'cta' => 'Choose a plan',
        ];
    }
}
