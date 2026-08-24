<?php

namespace App\Filament\Resources\FutureReadinessLeaderboards;

use App\Filament\Resources\FutureReadinessLeaderboards\Pages\ListFutureReadinessLeaderboards;
use App\Filament\Resources\FutureReadinessLeaderboards\Tables\FutureReadinessLeaderboardTable;

use App\Models\Child;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FutureReadinessLeaderboardResource extends Resource
{
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $model = Child::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedTrophy;

    protected static ?string $navigationLabel =
        'Future Readiness';

    protected static string|\UnitEnum|null $navigationGroup =
        'Analytics';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return FutureReadinessLeaderboardTable::configure($table);
    }

    /**
     * @return Builder<Child>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['skillProgress', 'badges'])
            ->withAvg('skillProgress as future_readiness_avg', 'progress_percentage');
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListFutureReadinessLeaderboards::route('/'),
        ];
    }
}