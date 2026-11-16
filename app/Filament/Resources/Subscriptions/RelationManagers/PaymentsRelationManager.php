<?php

namespace App\Filament\Resources\Subscriptions\RelationManagers;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Payments that funded this subscription. */
class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

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
            ->recordTitleAttribute('reference')
            ->defaultSort('id', 'desc')
            ->recordUrl(fn (Payment $record): string => PaymentResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i'),

                TextColumn::make('reference')
                    ->copyable(),

                TextColumn::make('amount_minor')
                    ->label('Amount')
                    ->state(fn (Payment $record): string => $record->formattedAmount()),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->color(fn (string $state): string => match ($state) {
                        Payment::STATUS_SUCCEEDED => 'success',
                        Payment::STATUS_PENDING, Payment::STATUS_PROCESSING => 'warning',
                        Payment::STATUS_FAILED => 'danger',
                        default => 'gray',
                    }),
            ]);
    }
}