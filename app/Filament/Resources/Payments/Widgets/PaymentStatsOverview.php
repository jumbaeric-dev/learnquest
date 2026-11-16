<?php

namespace App\Filament\Resources\Payments\Widgets;

use App\Models\Payment;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class PaymentStatsOverview extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('view payments');
    }

    protected function getStats(): array
    {
        $timezone = config('learnquest.daily_challenges.timezone') ?: config('app.timezone');

        // Query bindings are not timezone-converted, so convert to the app timezone first.
        $monthStart = now($timezone)->startOfMonth()->setTimezone(config('app.timezone'));

        $revenue = Payment::query()
            ->succeeded()
            ->where('paid_at', '>=', $monthStart)
            ->selectRaw('currency, SUM(amount_minor) as total, COUNT(*) as payments')
            ->groupBy('currency')
            ->orderBy('currency')
            ->get();

        $stats = [];

        foreach ($revenue as $row) {
            $stats[] = Stat::make("Revenue this month ({$row->currency})", Money::format((int) $row->total, $row->currency))
                ->description(((int) $row->payments) . ' paid ' . Str::plural('payment', (int) $row->payments))
                ->color('success');
        }

        if ($stats === []) {
            $stats[] = Stat::make('Revenue this month', '-')
                ->description('No paid payments yet')
                ->color('gray');
        }

        $stuck = Payment::query()->stuck()->count();

        $stats[] = Stat::make('Unsettled for over an hour', $stuck)
            ->description('Pending or processing')
            ->color($stuck > 0 ? 'warning' : 'gray');

        $late = Payment::query()
            ->lateSettlements()
            ->where('paid_at', '>=', now()->subDays(30))
            ->count();

        $stats[] = Stat::make('Late settlements (30 days)', $late)
            ->description('Paid after the checkout window')
            ->color($late > 0 ? 'warning' : 'gray');

        return $stats;
    }
}