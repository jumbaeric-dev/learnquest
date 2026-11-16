<?php

namespace App\Filament\Resources\Subscriptions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Audit trail of complimentary time for this subscription. */
class GrantsRelationManager extends RelationManager
{
    protected static string $relationship = 'grants';

    protected static ?string $title = 'Complimentary time given';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return (bool) auth()->user()?->can('view payments');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M j, Y H:i'),

                TextColumn::make('grantedBy.name')
                    ->label('Granted by')
                    ->placeholder('(account removed)'),

                TextColumn::make('days')
                    ->label('Added')
                    ->formatStateUsing(fn (int $state): string => "+{$state} days"),

                TextColumn::make('previous_ends_at')
                    ->label('Was')
                    ->dateTime('M j, Y')
                    ->placeholder('New'),

                TextColumn::make('new_ends_at')
                    ->label('Now ends')
                    ->dateTime('M j, Y'),

                TextColumn::make('reason')
                    ->wrap(),
            ]);
    }
}