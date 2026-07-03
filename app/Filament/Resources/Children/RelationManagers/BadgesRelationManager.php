<?php

namespace App\Filament\Resources\Children\RelationManagers;

use App\Models\Badge;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BadgesRelationManager extends RelationManager
{
    protected static string $relationship = 'badges';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('xp_reward')
                    ->label('XP'),

                TextColumn::make('pivot.earned_at')
                    ->label('Earned')
                    ->dateTime(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Award Badge')
                    ->recordSelectSearchColumns(['name'])
                    ->recordTitle(fn($record) => $record->name)
                    ->preloadRecordSelect()
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
