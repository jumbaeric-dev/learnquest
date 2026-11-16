<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Models\Subscription;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Subscription')
                ->columns(3)
                ->components([
                    TextEntry::make('user.name')->label('Parent'),
                    TextEntry::make('user.email')->label('Email')->copyable(),
                    TextEntry::make('plan.name')->label('Plan'),

                    TextEntry::make('state')
                        ->label('State')
                        ->state(fn (Subscription $record): string => $record->stateLabel())
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'Active' => 'success',
                            'Trial' => 'info',
                            'Complimentary' => 'primary',
                            'Grace period' => 'warning',
                            default => 'gray',
                        }),

                    TextEntry::make('starts_at')->label('Started')->dateTime(),
                    TextEntry::make('ends_at')->label('Access until')->dateTime(),

                    TextEntry::make('gateway')
                        ->formatStateUsing(fn (?string $state): string => ucfirst((string) $state))
                        ->placeholder('-'),

                    TextEntry::make('gateway_reference')
                        ->label('Gateway reference')
                        ->copyable()
                        ->placeholder('-'),

                    TextEntry::make('price')
                        ->label('Purchased as')
                        ->state(fn (Subscription $record): ?string => $record->price
                            ? $record->price->label() . ' · ' . $record->price->formatted()
                            : null)
                        ->placeholder('Not purchased (trial or complimentary)'),

                    TextEntry::make('canceled_at')->dateTime()->placeholder('-'),
                    TextEntry::make('created_at')->dateTime(),
                ]),
        ]);
    }
}