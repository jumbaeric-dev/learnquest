<?php

namespace App\Filament\Resources\Payments\RelationManagers;

use App\Models\WebhookEvent;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Provider events for one payment. Raw payloads are deliberately not shown. */
class WebhookEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'webhookEvents';

    protected static ?string $title = 'Provider events';

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
            ->recordTitleAttribute('event_id')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('M j, Y H:i:s'),

                TextColumn::make('event_type')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->color(fn (string $state): string => match ($state) {
                        WebhookEvent::STATUS_PROCESSED => 'success',
                        WebhookEvent::STATUS_DUPLICATE => 'gray',
                        WebhookEvent::STATUS_REJECTED, WebhookEvent::STATUS_FAILED => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('signature_verified_at')
                    ->label('Verified')
                    ->dateTime('H:i:s')
                    ->placeholder('-'),

                TextColumn::make('attempts'),

                TextColumn::make('failure_reason')
                    ->wrap()
                    ->placeholder('-'),
            ]);
    }
}