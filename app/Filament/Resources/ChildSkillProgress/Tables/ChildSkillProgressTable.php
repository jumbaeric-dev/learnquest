<?php

namespace App\Filament\Resources\ChildSkillProgress\Tables;

use App\Models\Child;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChildSkillProgressTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('child.full_name')
                    ->label('Child')
                    ->searchable([
                        'children.first_name',
                        'children.last_name',
                    ]),

                TextColumn::make('skill.name')
                    ->label('Skill')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('xp')
                    ->sortable()
                    ->badge(),

                TextColumn::make('level')
                    ->sortable()
                    ->badge(),

                ViewColumn::make('progress_percentage')
                    ->label('Progress')
                    ->view('filament.tables.columns.progress-bar'),

                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated'),

            ])
            ->filters([
                SelectFilter::make('skill')
                    ->relationship('skill', 'name'),

                SelectFilter::make('child')
                    ->options(
                        Child::query()
                            ->get()
                            ->mapWithKeys(fn(Child $child) => [
                                $child->id => $child->full_name,
                            ])
                    ),
            ])
            ->defaultSort(
                'xp',
                'desc'
            );
    }
}
