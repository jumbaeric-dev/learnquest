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

/**
 * The single place that answers "what is this family allowed to do?".
 *
 * Everything else (Livewire components, content access, Nova) asks this
 * service. Nothing hardcodes plan names.
 *
 * While billing is not enforced, nobody is restricted at all.
 */
class EntitlementService
{
  /** @var array<int, ?Subscription> */
  private array $subscriptions = [];

  public function enforced(): bool
  {
    return (bool) config("learnquest.billing.enforced", false);
  }

  /** Forget cached lookups after a subscription changes. */
  public function forget(?User $parent = null): void
  {
    if ($parent) {
      unset($this->subscriptions[$parent->id]);

      return;
    }

    $this->subscriptions = [];
  }

  public function currentSubscription(User $parent): ?Subscription
  {
    if (!array_key_exists($parent->id, $this->subscriptions)) {
      $this->subscriptions[$parent->id] = Subscription::query()
        ->current()
        ->where("user_id", $parent->id)
        ->with("plan")
        ->orderByDesc("ends_at")
        ->first();
    }

    return $this->subscriptions[$parent->id];
  }

  public function planKeyFor(User $parent): string
  {
    if (!$this->enforced()) {
      return (string) config("learnquest.billing.top_plan", "premium");
    }

    return $this->currentSubscription($parent)?->plan?->key ??
      (string) config("learnquest.billing.default_plan", "free");
  }

  public function planName(User $parent): string
  {
    $key = $this->planKeyFor($parent);

    return Plan::query()->where("key", $key)->value("name") ??
      Str::headline($key);
  }

  /** A numeric limit for the parent's plan. null means unlimited. */
  public function limit(User $parent, string $limit): ?int
  {
    if (!$this->enforced()) {
      return null;
    }

    $value = config(
      "learnquest.billing.plans.{$this->planKeyFor($parent)}.limits.{$limit}"
    );

    return $value === null ? null : (int) $value;
  }

  /** Same as limit(), resolved through the child's parent. */
  public function limitForChild(Child $child, string $limit): ?int
  {
    $parent = $child->parent;

    // Admin-created children without a parent are never restricted.
    return $parent ? $this->limit($parent, $limit) : null;
  }

  public function allows(User $parent, string $feature): bool
  {
    if (!$this->enforced()) {
      return true;
    }

    return (bool) config(
      "learnquest.billing.plans.{$this->planKeyFor($parent)}.features.{$feature}",
      false
    );
  }

  public function canAddChild(User $parent): bool
  {
    $limit = $this->limit($parent, "children");

    return $limit === null || $parent->children()->count() < $limit;
  }

  public function childLimitMessage(User $parent): string
  {
    $limit = (int) $this->limit($parent, "children");

    return sprintf(
      "Your %s plan includes %d %s. Upgrade to add more.",
      $this->planName($parent),
      $limit,
      $limit === 1 ? "child" : "children"
    );
  }

  public function assertCanAddChild(User $parent): void
  {
    if (!$this->canAddChild($parent)) {
      throw new PlanLimitReachedException($this->childLimitMessage($parent));
    }
  }

  /**
   * Whether the child's plan includes this lesson. Free plans include only
   * the first N modules (by position) of each course.
   */
  public function isLessonIncluded(Child $child, Lesson $lesson): bool
  {
    $allowed = $this->limitForChild($child, "modules_per_course");

    if ($allowed === null) {
      return true;
    }

    $lesson->loadMissing("module");

    $module = $lesson->module;

    if (!$module) {
      return true;
    }

    return CourseModule::query()
      ->where("course_id", $module->course_id)
      ->orderBy("position")
      ->orderBy("id")
      ->limit($allowed)
      ->pluck("id")
      ->contains($module->id);
  }

  public function assertLessonIncluded(Child $child, Lesson $lesson): void
  {
    if (!$this->isLessonIncluded($child, $lesson)) {
      throw new ContentLockedException(
        "This lesson is not included in the family's current plan."
      );
    }
  }

  /**
   * Everything the parent Billing page needs about the current plan.
   *
   * @return array<string, mixed>
   */
  public function summary(User $parent): array
  {
    $subscription = $this->enforced()
      ? $this->currentSubscription($parent)
      : null;

    $status = match (true) {
      $subscription === null => "free",
      $subscription->isTrial() => "trial",
      $subscription->status === Subscription::STATUS_CANCELED => "canceled",
      default => "active",
    };

    return [
      "enforced" => $this->enforced(),
      "plan_key" => $this->planKeyFor($parent),
      "plan_name" => $this->planName($parent),
      "status" => $status,
      "ends_at" => $subscription?->ends_at,
      "days_left" => $subscription
        ? max(0, (int) ceil(now()->diffInSeconds($subscription->ends_at, false) / 86400))
        : null,
      "children_used" => $parent->children()->count(),
      "children_limit" => $this->limit($parent, "children"),
    ];
  }
}