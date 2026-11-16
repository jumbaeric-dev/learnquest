<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Filament\Resources\Subscriptions\Actions\GrantComplimentaryTimeAction;
use App\Models\Plan;
use App\Models\Subscription;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('ends_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Parent')
                    ->description(fn(Subscription $record): ?string => $record->user?->email)
                    ->searchable(query: fn(Builder $query, string $search): Builder => $query->whereHas(
                        'user',
                        fn(Builder $user): Builder => $user
                            ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                            ->orWhereRaw('LOWER(email) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                    )),

                TextColumn::make('plan.name')
                    ->label('Plan'),

                TextColumn::make('state')
                    ->label('State')
                    ->state(fn(Subscription $record): string => $record->stateLabel())
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Active' => 'success',
                        'Trial' => 'info',
                        'Complimentary' => 'primary',
                        'Grace period' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('ends_at')
                    ->label('Access until')
                    ->dateTime('M j, Y H:i')
                    ->description(function (Subscription $record): ?string {
                        if (! $record->isCurrent()) {
                            return null;
                        }

                        $days = max(1, (int) ceil(now()->diffInSeconds($record->ends_at, false) / 86400));

                        return $days === 1 ? 'Ends within a day' : "{$days} days left";
                    })
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('Started')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('gateway')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn(?string $state): string => ucfirst((string) $state))
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('current')
                    ->label('Active right now')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->current()),

                Filter::make('ending_soon')
                    ->label('Ending within 7 days')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query
                        ->current()
                        ->where('ends_at', '<=', now()->addDays(7))),

                Filter::make('trial')
                    ->label('Trials')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->where('status', Subscription::STATUS_TRIALING)),

                Filter::make('complimentary')
                    ->label('Complimentary')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->where('gateway', 'complimentary')),

                Filter::make('grace')
                    ->label('In grace period')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->inGrace()),

                SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->options(fn(): array => Plan::query()->orderBy('sort_order')->pluck('name', 'id')->all()),
            ])
            ->recordActions([
                ViewAction::make(),
                GrantComplimentaryTimeAction::make()->label('Grant time'),
            ])
            ->toolbarActions([]);
    }
}
