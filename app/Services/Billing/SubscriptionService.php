<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\SubscriptionGrant;
use App\Models\User;
use App\Notifications\ComplimentaryTimeGrantedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Subscription lifecycle rules. Payment verification is deliberately external. */
class SubscriptionService
{
    public function __construct(private readonly EntitlementService $entitlements) {}

    public function startTrialIfEligible(User $parent): ?Subscription
    {
        if (!$this->entitlements->enforced() || !$parent->isParent()) {
            return null;
        }

        return DB::transaction(function () use ($parent) {
            $lockedParent = User::query()->lockForUpdate()->findOrFail($parent->id);
            if (Subscription::query()->where('user_id', $lockedParent->id)->exists()) {
                return null;
            }

            $plan = Plan::query()
                ->active()
                ->where('key', config('learnquest.billing.trial.plan', 'premium'))
                ->first();
            if (!$plan) {
                return null;
            }

            $days = max(1, (int) config('learnquest.billing.trial.days', 14));
            $subscription = Subscription::create([
                'user_id' => $lockedParent->id,
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_TRIALING,
                'starts_at' => now(),
                'ends_at' => now()->addDays($days),
                'auto_renews' => false,
            ]);

            $this->entitlements->forget($lockedParent);
            return $subscription;
        });
    }

    /**
     * Apply a verified purchase or manual renewal. Existing same-plan access is
     * extended; a plan change replaces the current entitlement from now.
     */
    public function activate(
        User $parent,
        PlanPrice $price,
        ?string $gateway = null,
        ?string $reference = null,
        bool $autoRenews = false
    ): Subscription {
        if (!$parent->isParent()) {
            throw ValidationException::withMessages(['plan' => 'Only parent accounts can hold subscriptions.']);
        }
        if ($autoRenews) {
            throw ValidationException::withMessages(['renewal' => 'Automatic renewal is not available. Renewals are manual.']);
        }

        $subscription = DB::transaction(function () use ($parent, $price, $gateway, $reference) {
            $lockedParent = User::query()->lockForUpdate()->findOrFail($parent->id);
            $validPrice = PlanPrice::query()
                ->with('plan')
                ->whereKey($price->id)
                ->where('is_active', true)
                ->first();

            if (!$validPrice || !$validPrice->plan || !$validPrice->plan->is_active) {
                throw ValidationException::withMessages(['price' => 'This plan price is no longer available.']);
            }
            if ($validPrice->months < 1 || !in_array($validPrice->currency, (array) config('learnquest.billing.currencies', ['KES']), true)) {
                throw ValidationException::withMessages(['price' => 'This plan price has invalid billing terms.']);
            }
            if ($validPrice->plan->key === config('learnquest.billing.default_plan', 'free')) {
                throw ValidationException::withMessages(['plan' => 'The Free plan cannot be purchased.']);
            }

            $now = now();
            $current = Subscription::query()
                ->current()
                ->where('user_id', $lockedParent->id)
                ->orderByDesc('ends_at')
                ->lockForUpdate()
                ->get();

            $samePlan = $current->firstWhere('plan_id', $validPrice->plan_id);

            // A paid plan that just ended is still inside its grace period. Renew it
            // from its original end date so coverage is continuous.
            $graceRow = null;
            if (!$samePlan) {
                $graceRow = Subscription::query()
                    ->inGrace()
                    ->where('user_id', $lockedParent->id)
                    ->where('plan_id', $validPrice->plan_id)
                    ->orderByDesc('ends_at')
                    ->lockForUpdate()
                    ->first();
            }

            foreach ($current as $active) {
                if ($active->isTrial() || $active->plan_id !== $validPrice->plan_id || ($samePlan && $active->id !== $samePlan->id)) {
                    $active->forceFill(['ends_at' => $now, 'auto_renews' => false])->save();
                }
            }

            $renewable = $samePlan ?? $graceRow;

            if ($renewable && !$renewable->isTrial()) {
                $base = ($renewable->ends_at->greaterThan($now) || $renewable->inGrace())
                    ? $renewable->ends_at->copy()
                    : $now->copy();

                $renewable->forceFill([
                    'plan_price_id' => $validPrice->id,
                    'status' => Subscription::STATUS_ACTIVE,
                    'ends_at' => $base->addMonthsNoOverflow($validPrice->months),
                    'canceled_at' => null,
                    'auto_renews' => false,
                    'gateway' => $gateway ?? $renewable->gateway,
                    'gateway_reference' => $reference ?? $renewable->gateway_reference,
                ])->save();

                return $renewable;
            }

            return Subscription::create([
                'user_id' => $lockedParent->id,
                'plan_id' => $validPrice->plan_id,
                'plan_price_id' => $validPrice->id,
                'status' => Subscription::STATUS_ACTIVE,
                'starts_at' => $now,
                'ends_at' => $now->copy()->addMonthsNoOverflow($validPrice->months),
                'auto_renews' => false,
                'gateway' => $gateway,
                'gateway_reference' => $reference,
            ]);
        });

        $this->entitlements->forget($parent);
        return $subscription;
    }

    /**
     * Give a parent complimentary time. Recorded in subscription_grants.
     *
     * - Same-plan paid or complimentary access is extended from its end date.
     * - A trial is ended, and its remaining days are kept at the start of the grant.
     * - Otherwise a new complimentary subscription starts now.
     */
    public function grantComplimentary(
        User $parent,
        Plan $plan,
        int $days,
        string $reason,
        User $grantedBy
    ): SubscriptionGrant {
        if (!$grantedBy->can('manage payments')) {
            throw new AuthorizationException('You are not allowed to grant complimentary time.');
        }

        $reason = trim($reason);
        $maxDays = max(1, (int) config('learnquest.billing.grants.max_days', 365));

        if (!$parent->isParent()) {
            throw ValidationException::withMessages(['user_id' => 'Only parent accounts can be given a plan.']);
        }
        if ($days < 1 || $days > $maxDays) {
            throw ValidationException::withMessages(['days' => "Choose between 1 and {$maxDays} days."]);
        }
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 500) {
            throw ValidationException::withMessages(['reason' => 'Please explain why, in 10 to 500 characters.']);
        }

        $grant = DB::transaction(function () use ($parent, $plan, $days, $reason, $grantedBy) {
            $lockedParent = User::query()->lockForUpdate()->findOrFail($parent->id);

            $validPlan = Plan::query()->active()->whereKey($plan->id)->first();
            if (!$validPlan || $validPlan->key === config('learnquest.billing.default_plan', 'free')) {
                throw ValidationException::withMessages(['plan_id' => 'Choose a paid, active plan.']);
            }

            $now = now();
            $current = Subscription::query()
                ->current()
                ->where('user_id', $lockedParent->id)
                ->orderByDesc('ends_at')
                ->lockForUpdate()
                ->get();

            $samePlan = $current->firstWhere('plan_id', $validPlan->id);

            $graceRow = null;

            if (! $samePlan) {
                $graceRow = Subscription::query()
                    ->inGrace()
                    ->where('user_id', $lockedParent->id)
                    ->where('plan_id', $validPlan->id)
                    ->orderByDesc('ends_at')
                    ->lockForUpdate()
                    ->first();
            }

            $base = $now->copy();

            foreach ($current as $active) {
                if ($active->isTrial() || $active->plan_id !== $validPlan->id || ($samePlan && $active->id !== $samePlan->id)) {
                    // Keep the remaining days of a same-plan trial at the start of the grant.
                    if ($active->plan_id === $validPlan->id && $active->ends_at->greaterThan($base)) {
                        $base = $active->ends_at->copy();
                    }

                    $active->forceFill(['ends_at' => $now, 'auto_renews' => false])->save();
                }
            }

            $previousEndsAt = null;

            $renewable = $samePlan ?? $graceRow;

            if ($renewable && !$renewable->isTrial()) {
                $previousEndsAt = $renewable->ends_at->copy();
                $start = ($renewable->ends_at->greaterThan($now) || $renewable->inGrace())
                    ? $renewable->ends_at->copy()
                    : $now->copy();

                $renewable->forceFill([
                    'status' => Subscription::STATUS_ACTIVE,
                    'ends_at' => $start->addDays($days),
                    'canceled_at' => null,
                    'auto_renews' => false,
                ])->save();

                $subscription = $renewable;
            } else {
                $subscription = Subscription::create([
                    'user_id' => $lockedParent->id,
                    'plan_id' => $validPlan->id,
                    'plan_price_id' => null,
                    'status' => Subscription::STATUS_ACTIVE,
                    'starts_at' => $now,
                    'ends_at' => $base->addDays($days),
                    'auto_renews' => false,
                    'gateway' => 'complimentary',
                    'gateway_reference' => null,
                ]);
            }

            return SubscriptionGrant::create([
                'subscription_id' => $subscription->id,
                'user_id' => $lockedParent->id,
                'granted_by' => $grantedBy->id,
                'plan_id' => $validPlan->id,
                'days' => $days,
                'previous_ends_at' => $previousEndsAt,
                'new_ends_at' => $subscription->ends_at,
                'reason' => $reason,
            ]);
        });

        $this->entitlements->forget($parent);

        try {
            $parent->notify(new ComplimentaryTimeGrantedNotification($grant));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $grant;
    }
}
