<?php

namespace App\Filament\Widgets;

use App\Models\Skill;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;

class WeakestSkillsWidget extends TableWidget
{
    protected static ?string $heading =
    'Future Skills Needing Attention';

    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Skill::query()
            )
            ->columns([

                TextColumn::make('name')
                    ->label('Skill'),

                TextColumn::make('average_progress')
                    ->label('Average Progress')
                    ->suffix('%')
                    ->badge()
                    ->state(function ($record) {

                        return round(
                            $record->childProgress()
                                ->avg('progress_percentage') ?? 0,
                            2
                        );
                    })
                    ->color(fn($state) => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'info',
                        $state >= 40 => 'warning',
                        default => 'danger',
                    }),

            ]);
    }

    public function getTableRecordsPerPage(): int|string|null
    {
        return 5;
    }
}
