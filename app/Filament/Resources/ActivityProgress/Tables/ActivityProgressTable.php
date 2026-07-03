<?php

namespace App\Filament\Resources\ActivityProgress\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class ActivityProgressTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('child.first_name')
                    ->label('Child')
                    ->searchable(),

                TextColumn::make('activity.title')
                    ->label('Activity')
                    ->searchable(),

                IconColumn::make('completed')
                    ->boolean(),

                TextColumn::make('score')
                    ->sortable(),

                TextColumn::make('xp_earned')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),

            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}