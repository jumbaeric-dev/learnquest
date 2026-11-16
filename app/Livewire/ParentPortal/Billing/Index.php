<?php

namespace App\Livewire\ParentPortal\Billing;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\BillingCheckoutService;
use App\Services\Billing\EntitlementService;
use App\Services\Billing\PaymentReconciliationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use App\Services\Billing\PaymentGatewayRegistry;

class Index extends Component
{
    public string $currency = 'KES';

    /**
     * One idempotency key per price, so switching billing periods never
     * reuses another price's attempt.
     *
     * @var array<int, string>
     */
    public array $checkoutKeys = [];

    public ?string $notice = null;

    public bool $showAllPayments = false;

    public function togglePayments(): void
    {
        $this->showAllPayments = ! $this->showAllPayments;
    }
    public function mount(): void
    {
        $this->parent();

        $currencies = $this->currencies();
        $default = (string) config('learnquest.billing.default_currency', 'KES');

        $this->currency = in_array($default, $currencies, true)
            ? $default
            : ($currencies[0] ?? 'KES');

        // PayPal redirects are only a prompt to verify the matching order server-side.
        // The browser-supplied token is never treated as proof of payment.
        $paypalAction = request()->query('paypal');
        if ($paypalAction === 'return') {
            $this->checkPaypalReturn(request()->query('token'));
        } elseif ($paypalAction === 'cancel') {
            $this->notice = 'Checkout was cancelled. No payment has been confirmed.';
        } else {
            $this->checkPendingPayments();
        }
    }

    public function setCurrency(string $currency): void
    {
        abort_unless(in_array($currency, $this->currencies(), true), 422);

        $this->currency = $currency;
    }

    public function refreshPayments(): void
    {
        $this->checkPendingPayments();
    }

    public function startCheckout(int $priceId)
    {
        $parent = $this->parent();
        $key = $this->checkoutKeys[$priceId] ??= (string) Str::uuid();

        try {
            $checkout = app(BillingCheckoutService::class)->startCheckout(
                parent: $parent,
                priceId: $priceId,
                idempotencyKey: $key,
            );

            return redirect()->away($checkout->checkoutUrl);
        } catch (ValidationException $exception) {
            // A stale or already-used attempt: forget it so the next click starts fresh.
            unset($this->checkoutKeys[$priceId]);

            throw $exception;
        } catch (PaymentGatewayException $exception) {
            report($exception);

            $this->addError(
                'checkout',
                'We could not start checkout just now. Your payment has not been confirmed. Please try again later.'
            );

            return null;
        }
    }

    private function checkPaypalReturn(mixed $orderId): void
    {
        if (! is_string($orderId) || ! preg_match('/^[A-Z0-9]{1,128}$/', $orderId)) {
            $this->notice = 'We could not match that PayPal return to a payment. You can refresh your payment history.';

            return;
        }

        $payment = Payment::query()
            ->where('user_id', $this->parent()->id)
            ->where('provider', 'paypal')
            ->where('metadata->paypal_order_id', $orderId)
            ->pending()
            ->first();

        if (! $payment) {
            $this->notice = 'We could not find a pending payment for that PayPal order. Your plan has not been changed.';

            return;
        }

        try {
            $result = app(PaymentReconciliationService::class)->reconcile($payment);
        } catch (PaymentGatewayException | ValidationException $exception) {
            Log::info('PayPal return verification deferred.', [
                'reference' => $payment->reference,
                'message' => mb_substr($exception->getMessage(), 0, 180),
            ]);
            $this->notice = 'PayPal is still confirming your payment. Refresh this page shortly to check again.';

            return;
        }

        $this->notice = match ($result['status'] ?? null) {
            'succeeded' => 'Payment received. Thank you! Your plan is now active.',
            'failed' => 'PayPal could not complete that payment. You can try again below.',
            default => 'PayPal is still confirming your payment. Refresh this page shortly to check again.',
        };
    }

    private function checkPendingPayments(): void
    {
        $reconciler = app(PaymentReconciliationService::class);

        $payments = Payment::query()
            ->where('user_id', $this->parent()->id)
            ->pending()
            ->where('created_at', '>=', now()->subDay())
            ->latest('id')
            ->limit(2)
            ->get();

        foreach ($payments as $payment) {
            try {
                $result = $reconciler->reconcile($payment);
            } catch (PaymentGatewayException | ValidationException $exception) {
                Log::info('Billing page payment check deferred.', [
                    'reference' => $payment->reference,
                    'message' => mb_substr($exception->getMessage(), 0, 180),
                ]);

                continue;
            }

            if (($result['status'] ?? null) === 'succeeded') {
                $this->notice = 'Payment received. Thank you! Your plan is now active.';
            } elseif (($result['status'] ?? null) === 'failed') {
                $this->notice = 'That payment didn’t go through. You can try again below.';
            }
        }
    }

    /** @return array<int, string> */
    private function currencies(): array
    {
        return array_values((array) config('learnquest.billing.currencies', ['KES']));
    }

    private function parent(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $user->isParent(), 403);

        return $user;
    }

    public function render(): View
    {
        $parent = $this->parent();
        $summary = app(EntitlementService::class)->summary($parent);

        $provider = app(PaymentGatewayRegistry::class)->providerForCurrency($this->currency);
        $providerSettings = (array) config("learnquest.gateways.providers.{$provider}", []);
        $providerCurrencies = array_map('strtoupper', (array) ($providerSettings['currencies'] ?? []));
        $canUseCheckout = (bool) ($providerSettings['enabled'] ?? false)
            && in_array(strtoupper($this->currency), $providerCurrencies, true);
        $checkoutProviderLabel = match ($provider) {
            'paystack' => 'Paystack',
            'intasend' => 'IntaSend',
            'paypal' => 'PayPal',
            default => ucfirst($provider),
        };

        $pendingCount = Payment::query()
            ->where('user_id', $parent->id)
            ->pending()
            ->where('created_at', '>=', now()->subDay())
            ->get()
            ->filter(fn(Payment $payment) => match ($payment->provider) {
                'intasend' => filled(data_get($payment->metadata, 'intasend_invoice_id')),
                'paystack' => filled(data_get($payment->metadata, 'paystack_transaction_reference')),
                'paypal' => filled(data_get($payment->metadata, 'paypal_order_id')),
                default => false,
            })
            ->count();

        $plans = Plan::query()
            ->active()
            ->when(
                ! config('learnquest.billing.free_plan_enabled', true),
                fn($query) => $query->where('key', '!=', 'free')
            )
            ->orderBy('sort_order')
            ->with(['prices' => fn($query) => $query
                ->where('is_active', true)
                ->where('currency', $this->currency)
                ->orderBy('months')])
            ->get()
            ->map(fn(Plan $plan) => [
                'key' => $plan->key,
                'name' => $plan->name,
                'tagline' => $plan->tagline,
                'is_current' => $plan->key === $summary['plan_key'],
                'highlights' => (array) config("learnquest.billing.plans.{$plan->key}.highlights", []),
                'prices' => $plan->prices
                    ->map(fn($price) => [
                        'id' => $price->id,
                        'label' => $price->label(),
                        'amount' => $price->formatted(),
                    ])
                    ->all(),
            ])
            ->all();

        $historyTimezone = config('learnquest.daily_challenges.timezone') ?: config('app.timezone');

        $historyQuery = Payment::query()
            ->where('user_id', $parent->id)
            ->where(function ($query) {
                $query
                    ->whereIn('status', [
                        Payment::STATUS_SUCCEEDED,
                        Payment::STATUS_REFUNDED,
                        Payment::STATUS_PARTIALLY_REFUNDED,
                    ])
                    ->orWhere(fn($pending) => $pending->pending()->where('created_at', '>=', now()->subDays(2)))
                    ->orWhere(fn($failed) => $failed
                        ->where('status', Payment::STATUS_FAILED)
                        ->where('created_at', '>=', now()->subDays(7)));
            });

        $historyTotal = (clone $historyQuery)->count();

        $payments = $historyQuery
            ->with('plan')
            ->orderByDesc('id')
            ->limit($this->showAllPayments ? 50 : 5)
            ->get()
            ->map(fn(Payment $payment) => [
                'id' => $payment->id,
                'date' => ($payment->paid_at ?? $payment->created_at)->copy()->setTimezone($historyTimezone)->format('M j, Y'),
                'description' => ($payment->plan?->name ?? Str::headline($payment->plan_key)) . ' · ' . $payment->periodLabel(),
                'amount' => $payment->formattedAmount(),
                'status' => $payment->status,
                'reference' => $payment->reference,
            ])
            ->all();

        return view('livewire.parent-portal.billing.index', [
            'summary' => $summary,
            'plans' => $plans,
            'currencies' => $this->currencies(),
            'canUseCheckout' => $canUseCheckout,
            'checkoutProviderLabel' => $checkoutProviderLabel,
            'pendingCount' => $pendingCount,
            'payments' => $payments,
            'historyTotal' => $historyTotal,
        ]);
    }
}
