<?php

namespace App\Filament\Widgets;

use App\Models\Child;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;

class TopLearnersWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Top FR Learners';

    protected static ?int $sort = 3;

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                Child::query()
                    ->with('skillProgress')
                    ->withAvg('skillProgress as future_readiness_avg', 'progress_percentage')
                    ->orderByDesc('future_readiness_avg')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex(isFromZero: false),

                TextColumn::make('full_name')
                    ->label('Learner')
                    ->searchable(),

                TextColumn::make('future_readiness_avg')
                    ->label('Readiness')
                    ->formatStateUsing(fn (?float $state): string => (string) round($state ?? 0, 1))
                    ->suffix('%')
                    ->badge()
                    ->color(fn (?float $state): string => match (true) {
                        ($state ?? 0) >= 80 => 'success',
                        ($state ?? 0) >= 60 => 'info',
                        ($state ?? 0) >= 40 => 'warning',
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
