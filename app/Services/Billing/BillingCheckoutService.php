<?php

namespace App\Services\Billing;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\User;
use App\Services\Billing\DTO\CheckoutResult;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class BillingCheckoutService
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly PaymentGatewayRegistry $gateways,
    ) {}

    /** Start checkout through the configured provider-independent gateway. */
    public function startCheckout(
        User $parent,
        int $priceId,
        string $idempotencyKey,
        ?string $provider = null,
    ): CheckoutResult {
        return $this->startProviderCheckout($parent, $priceId, $idempotencyKey, $provider);
    }

    /** Backwards-compatible entry point while IntaSend is held in reserve. */
    public function startIntaSendCheckout(User $parent, int $priceId, string $idempotencyKey): CheckoutResult
    {
        return $this->startProviderCheckout($parent, $priceId, $idempotencyKey, 'intasend');
    }

    private function startProviderCheckout(
        User $parent,
        int $priceId,
        string $idempotencyKey,
        ?string $provider,
    ): CheckoutResult {
        if (! $parent->isParent()) {
            abort(403);
        }

        if (! Str::isUuid($idempotencyKey)) {
            throw ValidationException::withMessages([
                'checkout' => 'This checkout request is invalid. Refresh the page and try again.',
            ]);
        }

        $price = PlanPrice::query()
            ->with('plan')
            ->whereKey($priceId)
            ->where('is_active', true)
            ->first();

        if (! $price || ! $price->plan?->is_active) {
            throw ValidationException::withMessages([
                'checkout' => 'That plan price is no longer available. Refresh the page and try again.',
            ]);
        }

        if ($price->plan->key === config('learnquest.billing.default_plan', 'free')) {
            throw ValidationException::withMessages([
                'checkout' => 'The Free plan does not require checkout.',
            ]);
        }
        
        $provider ??= $this->gateways->providerForCurrency($price->currency);

        // Resolve before creating a payment so disabled or unsupported gateways
        // cannot leave an unnecessary pending payment behind.
        $gateway = $this->gateways->forProvider($provider, $price->currency);
        $payment = $this->payments->createPending(
            parent: $parent,
            price: $price,
            provider: $provider,
            idempotencyKey: $idempotencyKey,
        );

        if (! in_array($payment->status, [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING], true)) {
            throw ValidationException::withMessages([
                'checkout' => 'That checkout was already completed or closed. Please choose your plan again.',
            ]);
        }

        $metadata = is_array($payment->metadata) ? $payment->metadata : [];
        $urlKey = $provider . '_checkout_url';
        $startedKey = $provider . '_checkout_initiation_started_at';
        $providerReferenceKey = $provider . '_transaction_reference';

        $existingUrl = $metadata[$urlKey] ?? null;
        if (is_string($existingUrl) && $existingUrl !== '') {
            return new CheckoutResult(
                checkoutUrl: $existingUrl,
                providerReference: $metadata[$providerReferenceKey] ?? null,
                expiresAt: $payment->expires_at?->toDateTimeImmutable(),
                metadata: ['provider' => $provider, 'flow' => 'hosted_checkout'],
            );
        }

        // Never blindly repeat an ambiguous initialization request. The provider
        // may have created a transaction even if the response was lost.
        if (! empty($metadata[$startedKey])) {
            throw new PaymentGatewayException(
                'This checkout attempt is still being reconciled. Please try again shortly or contact support.'
            );
        }

        $metadata[$startedKey] = now()->toIso8601String();
        $payment->forceFill(['metadata' => $metadata])->save();

        $result = $gateway->initiateCheckout($payment);
        if ($result->interaction !== 'redirect' || ! $result->checkoutUrl) {
            throw new PaymentGatewayException('The selected gateway did not return a hosted checkout URL.');
        }

        $payment->refresh();
        $metadata = is_array($payment->metadata) ? $payment->metadata : [];
        $metadata[$urlKey] = $result->checkoutUrl;
        if ($result->providerReference) {
            $metadata[$providerReferenceKey] = $result->providerReference;
        }
        $payment->forceFill(['metadata' => $metadata])->save();

        return $result;
    }
}
