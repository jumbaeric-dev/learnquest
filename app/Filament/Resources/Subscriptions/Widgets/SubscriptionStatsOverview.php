<?php

namespace App\Filament\Resources\Subscriptions\Widgets;

use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubscriptionStatsOverview extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('view payments');
    }

    protected function getStats(): array
    {
        $current = Subscription::query()->current();

        $paying = (clone $current)
            ->where('status', '!=', Subscription::STATUS_TRIALING)
            ->where(fn($query) => $query->whereNull('gateway')->orWhere('gateway', '!=', 'complimentary'))
            ->count();

        $trials = (clone $current)->where('status', Subscription::STATUS_TRIALING)->count();
        $complimentary = (clone $current)->where('gateway', 'complimentary')->count();
        $endingSoon = (clone $current)->where('ends_at', '<=', now()->addDays(7))->count();
        $inGrace = Subscription::query()->inGrace()->count();
        return [
            Stat::make('Paying families', $paying)
                ->description('Paid access right now')
                ->color('success'),

            Stat::make('On trial', $trials)
                ->description('Free trial in progress')
                ->color('info'),

            Stat::make('Complimentary', $complimentary)
                ->description('Granted by an admin')
                ->color('primary'),

            Stat::make('Ending within 7 days', $endingSoon)
                ->description('Reminders go out automatically')
                ->color($endingSoon > 0 ? 'warning' : 'gray'),

            Stat::make('In grace period', $inGrace)
                ->description('Ended recently, still unlocked')
                ->color($inGrace > 0 ? 'warning' : 'gray'),
        ];
    }
}
