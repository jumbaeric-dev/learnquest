<?php

namespace App\Livewire\ParentPortal\Billing;

use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\EntitlementService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public string $currency = 'KES';

    public function mount(): void
    {
        $this->parent();

        $currencies = $this->currencies();
        $default = (string) config('learnquest.billing.default_currency', 'KES');

        $this->currency = in_array($default, $currencies, true)
            ? $default
            : ($currencies[0] ?? 'KES');
    }

    public function setCurrency(string $currency): void
    {
        abort_unless(in_array($currency, $this->currencies(), true), 422);

        $this->currency = $currency;
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

        $plans = Plan::query()
            ->active()
            ->orderBy('sort_order')
            ->with(['prices' => fn ($query) => $query
                ->where('is_active', true)
                ->where('currency', $this->currency)
                ->orderBy('months')])
            ->get()
            ->map(fn (Plan $plan) => [
                'key' => $plan->key,
                'name' => $plan->name,
                'tagline' => $plan->tagline,
                'is_current' => $plan->key === $summary['plan_key'],
                'highlights' => (array) config("learnquest.billing.plans.{$plan->key}.highlights", []),
                'prices' => $plan->prices
                    ->map(fn ($price) => [
                        'label' => $price->label(),
                        'amount' => $price->formatted(),
                    ])
                    ->all(),
            ])
            ->all();

        return view('livewire.parent-portal.billing.index', [
            'summary' => $summary,
            'plans' => $plans,
            'currencies' => $this->currencies(),
        ]);
    }
}