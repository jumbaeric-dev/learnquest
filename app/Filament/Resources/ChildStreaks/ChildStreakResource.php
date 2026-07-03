<?php

namespace App\Filament\Resources\ChildStreaks;

use App\Filament\Resources\ChildStreaks\Pages\ListChildStreaks;
use App\Filament\Resources\ChildStreaks\Schemas\ChildStreakForm;
use App\Filament\Resources\ChildStreaks\Tables\ChildStreaksTable;
use App\Models\ChildStreak;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChildStreakResource extends Resource
{
    protected static ?string $model = ChildStreak::class;

    protected static string|BackedEnum|null $navigationIcon =
    Heroicon::OutlinedFire;

    protected static string|\UnitEnum|null $navigationGroup =
    'Learners';

    protected static ?string $navigationLabel =
    'Streaks';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute =
    'child.full_name';

    public static function form(Schema $schema): Schema
    {
        return ChildStreakForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChildStreaksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChildStreaks::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
