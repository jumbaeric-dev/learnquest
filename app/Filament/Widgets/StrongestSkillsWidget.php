<?php

namespace App\Filament\Widgets;

use App\Models\Skill;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;

class StrongestSkillsWidget extends TableWidget
{
    protected static ?string $heading =
    'Strongest Skills';

    protected static ?int $sort = 4;

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
                    ->sortable()
                // ->defaultSort('name'),

            ]);
    }

    public function getTableRecordsPerPage(): int|string|null
    {
        return 5;
    }
}
