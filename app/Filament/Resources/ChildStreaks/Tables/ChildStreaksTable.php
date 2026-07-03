<?php

namespace App\Filament\Resources\ChildStreaks\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ChildStreaksTable
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

                TextColumn::make('current_streak')
                    ->badge()
                    ->sortable(),

                TextColumn::make('longest_streak')
                    ->badge()
                    ->sortable(),

                TextColumn::make('last_activity_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated'),

            ])
            ->defaultSort(
                'current_streak',
                'desc'
            );
    }
}
