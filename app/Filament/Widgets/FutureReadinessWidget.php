<?php

namespace App\Filament\Widgets;

use App\Models\Child;
use DB;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FutureReadinessWidget extends StatsOverviewWidget
{
    // protected int|string|array $columnSpan = 1;
    protected function getStats(): array
    {
        $children = Child::with('badges', 'skillProgress')->get();

        $averageReadiness = round(
            $children->avg('future_readiness_score') ?? 0,
            1
        );

        return [

            Stat::make(
                'Future Readiness',
                $averageReadiness . '%'
            )
                ->description('Average learner readiness')
                ->icon('heroicon-o-sparkles')
                ->color('success'),

            Stat::make(
                'Learners',
                Child::count()
            )
                ->description('Registered children')
                ->icon('heroicon-o-users'),

            Stat::make(
                'Total XP',
                number_format(
                    Child::sum('xp')
                )
            )
                ->description('XP earned platform-wide')
                ->icon('heroicon-o-star'),

            Stat::make(
                'Badges Awarded',
                DB::table('badge_child')->count()
            )
                ->description('Total badges earned')
                ->icon('heroicon-o-trophy'),

        ];
    }
}
