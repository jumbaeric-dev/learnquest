<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Payment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payment')
                ->columns(3)
                ->components([
                    TextEntry::make('reference')->copyable(),

                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => Str::headline($state))
                        ->color(fn (string $state): string => match ($state) {
                            Payment::STATUS_SUCCEEDED => 'success',
                            Payment::STATUS_PENDING, Payment::STATUS_PROCESSING => 'warning',
                            Payment::STATUS_FAILED => 'danger',
                            Payment::STATUS_REFUNDED, Payment::STATUS_PARTIALLY_REFUNDED => 'info',
                            default => 'gray',
                        }),

                    TextEntry::make('provider')
                        ->badge()
                        ->color('gray')
                        ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                    TextEntry::make('amount')
                        ->state(fn (Payment $record): string => $record->formattedAmount()),

                    TextEntry::make('plan')
                        ->state(fn (Payment $record): string => ($record->plan?->name ?? Str::headline($record->plan_key)) . ' · ' . $record->periodLabel()),

                    TextEntry::make('provider_transaction_id')
                        ->label('Provider transaction')
                        ->copyable()
                        ->placeholder('-'),

                    TextEntry::make('created_at')->dateTime(),
                    TextEntry::make('paid_at')->dateTime()->placeholder('-'),
                    TextEntry::make('expires_at')->dateTime()->placeholder('-'),
                    TextEntry::make('failed_at')->dateTime()->placeholder('-'),
                    TextEntry::make('failure_code')->placeholder('-'),

                    TextEntry::make('review_flag')
                        ->label('Review flag')
                        ->state(fn (Payment $record): ?string => $record->isLateSettlement()
                            ? 'Paid after the checkout window closed, or after the attempt was marked failed or cancelled. Access was granted because the money was received.'
                            : null)
                        ->badge()
                        ->color('warning')
                        ->placeholder('None')
                        ->columnSpanFull(),
                ]),

            Section::make('Parent')
                ->columns(3)
                ->components([
                    TextEntry::make('user.name')->label('Name'),
                    TextEntry::make('user.email')->label('Email')->copyable(),
                    TextEntry::make('user_id')->label('User ID'),
                ]),

            Section::make('Subscription')
                ->columns(4)
                ->components([
                    TextEntry::make('subscription.status')
                        ->label('Status')
                        ->badge()
                        ->placeholder('Not granted'),
                    TextEntry::make('subscription.starts_at')->label('Starts')->dateTime()->placeholder('-'),
                    TextEntry::make('subscription.ends_at')->label('Access until')->dateTime()->placeholder('-'),
                    TextEntry::make('subscription.gateway')->label('Gateway')->placeholder('-'),
                ]),
        ]);
    }
}