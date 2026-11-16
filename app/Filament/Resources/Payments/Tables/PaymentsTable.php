<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Resources\Payments\Actions\ReconcilePaymentAction;
use App\Models\Payment;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),

                TextColumn::make('reference')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('user.name')
                    ->label('Parent')
                    ->description(fn (Payment $record): ?string => $record->user?->email)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                        'user',
                        fn (Builder $user): Builder => $user
                            ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                            ->orWhereRaw('LOWER(email) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                    )),

                TextColumn::make('plan_key')
                    ->label('Plan')
                    ->formatStateUsing(fn (?string $state, Payment $record): string => Str::headline((string) $state) . ' · ' . $record->periodLabel()),

                TextColumn::make('amount_minor')
                    ->label('Amount')
                    ->state(fn (Payment $record): string => $record->formattedAmount())
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->color(fn (string $state): string => match ($state) {
                        Payment::STATUS_SUCCEEDED => 'success',
                        Payment::STATUS_PENDING, Payment::STATUS_PROCESSING => 'warning',
                        Payment::STATUS_FAILED => 'danger',
                        Payment::STATUS_REFUNDED, Payment::STATUS_PARTIALLY_REFUNDED => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('late_flag')
                    ->label('Flag')
                    ->state(fn (Payment $record): ?string => $record->isLateSettlement() ? 'Late payment' : null)
                    ->badge()
                    ->color('warning'),

                TextColumn::make('provider')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('paid_at')
                    ->dateTime('M j, Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('provider_transaction_id')
                    ->label('Provider transaction')
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options(collect([
                        Payment::STATUS_PENDING,
                        Payment::STATUS_PROCESSING,
                        Payment::STATUS_SUCCEEDED,
                        Payment::STATUS_FAILED,
                        Payment::STATUS_CANCELLED,
                        Payment::STATUS_REFUNDED,
                        Payment::STATUS_PARTIALLY_REFUNDED,
                    ])->mapWithKeys(fn (string $status): array => [$status => Str::headline($status)])->all()),

                SelectFilter::make('provider')
                    ->options(fn (): array => Payment::query()
                        ->distinct()
                        ->orderBy('provider')
                        ->pluck('provider', 'provider')
                        ->map(fn (string $provider): string => ucfirst($provider))
                        ->all()),

                SelectFilter::make('currency')
                    ->options(fn (): array => Payment::query()
                        ->distinct()
                        ->orderBy('currency')
                        ->pluck('currency', 'currency')
                        ->all()),

                Filter::make('late_settlement')
                    ->label('Late settlements')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->lateSettlements()),

                Filter::make('stuck')
                    ->label('Unsettled for over an hour')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->stuck()),
            ])
            ->recordActions([
                ViewAction::make(),
                ReconcilePaymentAction::make(),
            ])
            ->toolbarActions([]);
    }
}