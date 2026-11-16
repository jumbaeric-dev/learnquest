<?php

namespace App\Services\Billing;

use App\Exceptions\ContentLockedException;
use App\Exceptions\PlanLimitReachedException;
use App\Models\Child;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Str;

/** Central authority for family plan entitlements. */
class EntitlementService
{
    public function enforced(): bool
    {
        return (bool) config('learnquest.billing.enforced', false);
    }

    public function freePlanEnabled(): bool
    {
        return (bool) config('learnquest.billing.free_plan_enabled', true);
    }

    /** Existing families retain Free access after the public Free plan is disabled. */
    public function hasGrandfatheredFreeAccess(User $parent): bool
    {
        return (bool) $parent->free_plan_grandfathered;
    }

    /** Kept for callers after writes; lookups are no longer cached. */
    public function forget(?User $parent = null): void
    {
        // Intentionally empty: every lookup reads the current database state.
    }

    public function currentSubscription(User $parent): ?Subscription
    {
        return Subscription::query()
            ->current()
            ->where('user_id', $parent->id)
            ->with('plan')
            ->orderByDesc('ends_at')
            ->first()
            // Paid access that just ended keeps working through its grace period.
            ?? Subscription::query()
            ->inGrace()
            ->where('user_id', $parent->id)
            ->with('plan')
            ->orderByDesc('ends_at')
            ->first();
    }

    public function planKeyFor(User $parent): string
    {
        if (!$this->enforced()) {
            return (string) config('learnquest.billing.top_plan', 'premium');
        }

        $subscription = $this->currentSubscription($parent);
        if ($subscription?->plan?->key) {
            return $subscription->plan->key;
        }

        if ($this->freePlanEnabled() || $this->hasGrandfatheredFreeAccess($parent)) {
            return (string) config('learnquest.billing.default_plan', 'free');
        }

        return 'unavailable';
    }

    public function planName(User $parent): string
    {
        $key = $this->planKeyFor($parent);
        if ($key === 'unavailable') {
            return 'No active plan';
        }

        return Plan::query()->where('key', $key)->value('name') ?? Str::headline($key);
    }

    /** A null limit means unlimited; zero means no access. */
    public function limit(User $parent, string $limit): ?int
    {
        if (!$this->enforced()) {
            return null;
        }

        $planKey = $this->planKeyFor($parent);
        if ($planKey === 'unavailable') {
            return 0;
        }

        $value = config("learnquest.billing.plans.{$planKey}.limits.{$limit}");
        return $value === null ? null : (int) $value;
    }

    public function limitForChild(Child $child, string $limit): ?int
    {
        $parent = $child->parent;
        return $parent ? $this->limit($parent, $limit) : null;
    }

    public function allows(User $parent, string $feature): bool
    {
        if (!$this->enforced()) {
            return true;
        }

        $planKey = $this->planKeyFor($parent);
        if ($planKey === 'unavailable') {
            return false;
        }

        return (bool) config("learnquest.billing.plans.{$planKey}.features.{$feature}", false);
    }

    public function canAddChild(User $parent): bool
    {
        $limit = $this->limit($parent, 'children');
        return $limit === null || $parent->children()->count() < $limit;
    }

    public function childLimitMessage(User $parent): string
    {
        $limit = $this->limit($parent, 'children') ?? 0;
        return $this->planKeyFor($parent) === 'unavailable'
            ? 'Choose an available plan to add child profiles.'
            : sprintf('Your %s plan includes %d %s. Upgrade to add more.', $this->planName($parent), $limit, $limit === 1 ? 'child' : 'children');
    }

    public function assertCanAddChild(User $parent): void
    {
        if (!$this->canAddChild($parent)) {
            throw new PlanLimitReachedException($this->childLimitMessage($parent));
        }
    }

    public function isLessonIncluded(Child $child, Lesson $lesson): bool
    {
        $allowed = $this->limitForChild($child, 'modules_per_course');
        if ($allowed === null) {
            return true;
        }
        if ($allowed <= 0) {
            return false;
        }

        $lesson->loadMissing('module');
        $module = $lesson->module;
        if (!$module) {
            return true;
        }

        return CourseModule::query()
            ->where('course_id', $module->course_id)
            ->orderBy('position')
            ->orderBy('id')
            ->limit($allowed)
            ->pluck('id')
            ->contains($module->id);
    }

    public function assertLessonIncluded(Child $child, Lesson $lesson): void
    {
        if (!$this->isLessonIncluded($child, $lesson)) {
            throw new ContentLockedException("This lesson is not included in the family's current plan.");
        }
    }

    /** @return array<string, mixed> */
    public function summary(User $parent): array
    {
        $subscription = $this->enforced() ? $this->currentSubscription($parent) : null;
        $planKey = $this->planKeyFor($parent);
        $status = match (true) {
            $planKey === 'unavailable' => 'plan_required',
            $subscription === null => 'free',
            $subscription->status === Subscription::STATUS_CANCELED => 'canceled',
            $subscription->inGrace() => 'grace',
            $subscription->isTrial() => 'trial',
            default => 'active',
        };

        return [
            'enforced' => $this->enforced(),
            'plan_key' => $planKey,
            'plan_name' => $this->planName($parent),
            'status' => $status,
            'ends_at' => $subscription?->ends_at,
            'children_used' => $parent->children()->count(),
            'children_limit' => $this->limit($parent, 'children'),
            'free_plan_enabled' => $this->freePlanEnabled(),
            'days_left' => $subscription && ! $subscription->inGrace()
                ? max(0, (int) ceil(now()->diffInSeconds($subscription->ends_at, false) / 86400))
                : null,
            'grace_ends_at' => $subscription?->inGrace() ? $subscription->graceEndsAt() : null,
        ];
    }
}
