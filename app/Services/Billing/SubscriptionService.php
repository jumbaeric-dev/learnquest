<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Creates and extends subscriptions. Phase 2 payment webhooks call
 * activate() once a payment is verified. This phase moves no money.
 */
class SubscriptionService
{
  public function __construct(private readonly EntitlementService $entitlements)
  {
  }

  /**
   * Start the one-time, no-card trial for a parent.
   *
   * Returns null when billing is not enforced, the user is not a parent, the
   * trial plan is missing, or the parent has ever had a subscription.
   */
  public function startTrialIfEligible(User $parent): ?Subscription
  {
    if (!$this->entitlements->enforced() || !$parent->isParent()) {
      return null;
    }

    if (Subscription::query()->where("user_id", $parent->id)->exists()) {
      return null;
    }

    $plan = Plan::query()
      ->where("key", config("learnquest.billing.trial.plan", "premium"))
      ->first();

    if (!$plan) {
      return null;
    }

    $days = max(1, (int) config("learnquest.billing.trial.days", 14));

    $subscription = Subscription::create([
      "user_id" => $parent->id,
      "plan_id" => $plan->id,
      "status" => Subscription::STATUS_TRIALING,
      "starts_at" => now(),
      "ends_at" => now()->addDays($days),
      "auto_renews" => false,
    ]);

    $this->entitlements->forget($parent);

    return $subscription;
  }

  /**
   * Grant paid access for one price, whatever the payment method.
   *
   * - Renewing the same plan extends the existing end date.
   * - Paying during a trial ends the trial now and starts paid access now.
   * - Otherwise a new subscription starts now.
   */
  public function activate(
    User $parent,
    PlanPrice $price,
    ?string $gateway = null,
    ?string $reference = null,
    bool $autoRenews = false
  ): Subscription {
    $subscription = DB::transaction(function () use (
      $parent,
      $price,
      $gateway,
      $reference,
      $autoRenews
    ) {
      $now = now();

      $existing = Subscription::query()
        ->current()
        ->where("user_id", $parent->id)
        ->where("plan_id", $price->plan_id)
        ->orderByDesc("ends_at")
        ->first();

      if ($existing && $existing->isTrial()) {
        $existing->forceFill(["ends_at" => $now])->save();
        $existing = null;
      }

      if ($existing) {
        $existing
          ->forceFill([
            "plan_price_id" => $price->id,
            "status" => Subscription::STATUS_ACTIVE,
            "ends_at" => $existing->ends_at->copy()->addMonthsNoOverflow($price->months),
            "canceled_at" => null,
            "auto_renews" => $autoRenews,
            "gateway" => $gateway ?? $existing->gateway,
            "gateway_reference" => $reference ?? $existing->gateway_reference,
          ])
          ->save();

        return $existing;
      }

      return Subscription::create([
        "user_id" => $parent->id,
        "plan_id" => $price->plan_id,
        "plan_price_id" => $price->id,
        "status" => Subscription::STATUS_ACTIVE,
        "starts_at" => $now,
        "ends_at" => $now->copy()->addMonthsNoOverflow($price->months),
        "auto_renews" => $autoRenews,
        "gateway" => $gateway,
        "gateway_reference" => $reference,
      ]);
    });

    $this->entitlements->forget($parent);

    return $subscription;
  }
}