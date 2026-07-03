<?php

namespace App\Filament\Resources\FutureReadinessLeaderboards\Tables;

use App\Models\Child;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FutureReadinessLeaderboardTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->defaultSort('xp', 'desc')
            ->columns([

                TextColumn::make('rank')
                    ->label('#')
                    ->state(function ($record) {

                        return \App\Models\Child::all()
                            ->sortByDesc(
                                fn($child) =>
                                $child->future_readiness_score
                            )
                            ->values()
                            ->search(
                                fn($child) =>
                                $child->id === $record->id
                            ) + 1;
                    }),

                TextColumn::make('full_name')
                    ->label('Learner')
                    ->searchable(),

                TextColumn::make('future_readiness_score')
                    ->label('Future Readiness')
                    ->suffix('%')
                    ->badge(),

                TextColumn::make('xp')
                    ->badge(),

                TextColumn::make('level')
                    ->badge(),

                TextColumn::make('badges_count')
                    ->counts('badges')
                    ->label('Badges'),

                TextColumn::make('strongest_skill')
                    ->label('Strongest Skill')
                    ->state(
                        fn(Child $record) =>
                        \App\Services\FutureReadinessService::strongestSkill($record)
                    )
                    ->badge()
                    ->color('success'),

                TextColumn::make('weakest_skill')
                    ->label('Weakest Skill')
                    ->state(
                        fn(Child $record) =>
                        \App\Services\FutureReadinessService::weakestSkill($record)
                    )
                    ->badge()
                    ->color('warning'),
            ]);
    }
}
