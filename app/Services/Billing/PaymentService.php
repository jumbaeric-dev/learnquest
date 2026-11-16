<?php

namespace App\Services\Billing;

use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Creates auditable payment attempts; it never communicates with a gateway. */
class PaymentService
{
    private const PROVIDERS = ['intasend', 'paystack', 'paypal'];

    public function createPending(
        User $parent,
        PlanPrice $price,
        string $provider,
        ?string $idempotencyKey = null,
        ?int $expiresInMinutes = 30,
    ): Payment {
        if (!$parent->isParent()) {
            throw ValidationException::withMessages(['payment' => 'Only parent accounts can make payments.']);
        }
        if (!in_array($provider, self::PROVIDERS, true)) {
            throw ValidationException::withMessages(['provider' => 'The selected payment provider is not supported.']);
        }
        if ($idempotencyKey !== null && (strlen($idempotencyKey) > 100 || trim($idempotencyKey) === '')) {
            throw ValidationException::withMessages(['idempotency_key' => 'The idempotency key is invalid.']);
        }

        return DB::transaction(function () use ($parent, $price, $provider, $idempotencyKey, $expiresInMinutes) {
            $lockedParent = User::query()->lockForUpdate()->findOrFail($parent->id);

            if ($idempotencyKey !== null) {
                $existing = Payment::query()
                    ->where('user_id', $lockedParent->id)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();
                if ($existing) {
                    if ($existing->provider !== $provider || $existing->plan_price_id !== $price->id) {
                        throw ValidationException::withMessages(['idempotency_key' => 'This key has already been used for a different payment request.']);
                    }
                    return $existing;
                }
            }

            $validPrice = PlanPrice::query()->with('plan')->whereKey($price->id)->where('is_active', true)->first();
            if (!$validPrice || !$validPrice->plan?->is_active) {
                throw ValidationException::withMessages(['price' => 'This plan price is no longer available.']);
            }
            if ($validPrice->plan->key === config('learnquest.billing.default_plan', 'free')) {
                throw ValidationException::withMessages(['plan' => 'The Free plan cannot be purchased.']);
            }
            if ($validPrice->months < 1 || $validPrice->amount_minor < 1
                || !in_array($validPrice->currency, (array) config('learnquest.billing.currencies', ['KES']), true)) {
                throw ValidationException::withMessages(['price' => 'This plan price has invalid billing terms.']);
            }

            $reference = 'LQ-' . Str::upper(Str::random(30));

            return Payment::create([
                'user_id' => $lockedParent->id,
                'plan_id' => $validPrice->plan_id,
                'plan_price_id' => $validPrice->id,
                'plan_key' => $validPrice->plan->key,
                'duration_months' => $validPrice->months,
                'reference' => $reference,
                'idempotency_key' => $idempotencyKey,
                'provider' => $provider,
                'amount_minor' => $validPrice->amount_minor,
                'currency' => $validPrice->currency,
                'status' => Payment::STATUS_PENDING,
                'expires_at' => $expiresInMinutes === null ? null : now()->addMinutes(max(1, $expiresInMinutes)),
            ]);
        }, 3);
    }
}
