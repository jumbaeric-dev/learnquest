<?php

namespace App\Filament\Widgets;

use App\Models\Child;
// use App\Services\FutureReadinessService;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;

class TopLearnersWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;
    protected static ?string $heading =
    'Top FR Learners';

    protected static ?int $sort = 3;

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                Child::query()
                    ->with('skillProgress')
            )
            ->defaultSort('xp', 'desc')
            ->columns([

                TextColumn::make('rank')
                    ->label('#')
                    ->state(function ($record) {

                        return Child::with('skillProgress')
                            ->get()
                            ->sortByDesc(
                                fn($child) => $child->future_readiness_score
                            )
                            ->values()
                            ->search(
                                fn($child) => $child->id === $record->id
                            ) + 1;
                    }),

                TextColumn::make('full_name')
                    ->label('Learner')
                    ->searchable(),

                TextColumn::make('future_readiness_score')
                    ->label('Readiness')
                    ->suffix('%')
                    ->badge()
                    ->sortable()
                    ->color(fn($state) => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'info',
                        $state >= 40 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('xp')
                    ->badge()
                    ->sortable(),

                TextColumn::make('level')
                    ->badge()
                    ->sortable(),

            ]);
    }

    public function getTableRecordsPerPage(): int
    {
        return 5;
    }
}
