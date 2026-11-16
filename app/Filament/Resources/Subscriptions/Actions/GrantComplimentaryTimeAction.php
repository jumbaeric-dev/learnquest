<?php

namespace App\Filament\Resources\Subscriptions\Actions;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;

/**
 * Gives a parent free time and records who gave it and why. Used as a list
 * header action (pick any parent) and as a per-row action (parent prefilled).
 */
class GrantComplimentaryTimeAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'grant';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Grant complimentary time')
            ->icon(Heroicon::OutlinedGift)
            ->authorize(fn (): bool => (bool) auth()->user()?->can('manage payments'))
            ->modalHeading('Grant complimentary time')
            ->modalDescription('Adds free access on top of whatever the parent already has. It is recorded in the audit trail with your name and the reason.')
            ->modalSubmitActionLabel('Grant time')
            ->fillForm(fn (?Subscription $record = null): array => [
                'user_id' => $record?->user_id,
                'plan_id' => $record?->plan_id ?? $this->paidPlans()->keys()->first(),
                'days' => 30,
            ])
            ->schema([
                Select::make('user_id')
                    ->label('Parent')
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): array {
                        $like = '%' . mb_strtolower($search) . '%';

                        return User::query()
                            ->role('parent')
                            ->where(fn ($query) => $query
                                ->whereRaw('LOWER(name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(email) LIKE ?', [$like]))
                            ->orderBy('name')
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn (User $user): array => [$user->id => "{$user->name} ({$user->email})"])
                            ->all();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $user = User::query()->find($value);

                        return $user ? "{$user->name} ({$user->email})" : null;
                    }),

                Select::make('plan_id')
                    ->label('Plan')
                    ->required()
                    ->options(fn (): array => $this->paidPlans()->all()),

                TextInput::make('days')
                    ->label('Days of access')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->minValue(1)
                    ->maxValue((int) config('learnquest.billing.grants.max_days', 365)),

                Textarea::make('reason')
                    ->required()
                    ->minLength(10)
                    ->maxLength(500)
                    ->rows(3)
                    ->helperText('Who asked, and why. Visible to admins only.'),
            ])
            ->action(function (array $data): void {
                $admin = auth()->user();

                abort_unless($admin instanceof User && $admin->can('manage payments'), 403);

                try {
                    $grant = app(SubscriptionService::class)->grantComplimentary(
                        User::query()->findOrFail($data['user_id']),
                        Plan::query()->findOrFail($data['plan_id']),
                        (int) $data['days'],
                        (string) $data['reason'],
                        $admin,
                    );
                } catch (ValidationException $exception) {
                    Notification::make()
                        ->title('Could not grant time')
                        ->body((string) collect($exception->errors())->flatten()->first())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Complimentary time granted')
                    ->body("{$grant->days} days added. Access now runs until {$grant->new_ends_at->format('M j, Y')}.")
                    ->success()
                    ->send();
            });
    }

    /** @return \Illuminate\Support\Collection<int, string> id => name */
    private function paidPlans()
    {
        return Plan::query()
            ->active()
            ->where('key', '!=', config('learnquest.billing.default_plan', 'free'))
            ->orderBy('sort_order')
            ->pluck('name', 'id');
    }
}