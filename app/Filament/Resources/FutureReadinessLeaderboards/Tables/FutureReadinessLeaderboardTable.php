<?php

namespace App\Filament\Resources\FutureReadinessLeaderboards\Tables;

use App\Models\Child;
use App\Services\FutureReadinessService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FutureReadinessLeaderboardTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->defaultSort('future_readiness_avg', 'desc')
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex(isFromZero: false),

                TextColumn::make('full_name')
                    ->label('Learner')
                    ->searchable(),

                TextColumn::make('future_readiness_avg')
                    ->label('Future Readiness')
                    ->formatStateUsing(fn (?float $state): string => (string) round($state ?? 0, 1))
                    ->suffix('%')
                    ->badge()
                    ->sortable(),

                TextColumn::make('xp')
                    ->badge()
                    ->sortable(),

                TextColumn::make('level')
                    ->badge()
                    ->sortable(),

                TextColumn::make('badges_count')
                    ->counts('badges')
                    ->label('Badges'),

                TextColumn::make('strongest_skill')
                    ->label('Strongest Skill')
                    ->state(
                        fn (Child $record): ?string => FutureReadinessService::strongestSkill($record)
                    )
                    ->badge()
                    ->color('success'),

                TextColumn::make('weakest_skill')
                    ->label('Weakest Skill')
                    ->state(
                        fn (Child $record): ?string => FutureReadinessService::weakestSkill($record)
                    )
                    ->badge()
                    ->color('warning'),
            ]);
    }
}
