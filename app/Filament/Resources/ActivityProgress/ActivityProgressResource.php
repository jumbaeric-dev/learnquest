<?php

namespace App\Filament\Resources\ActivityProgress;

use App\Filament\Resources\ActivityProgress\Pages\CreateActivityProgress;
use App\Filament\Resources\ActivityProgress\Pages\EditActivityProgress;
use App\Filament\Resources\ActivityProgress\Pages\ListActivityProgress;
use App\Filament\Resources\ActivityProgress\Schemas\ActivityProgressForm;
use App\Filament\Resources\ActivityProgress\Tables\ActivityProgressTable;
use App\Models\ActivityProgress;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActivityProgressResource extends Resource
{
    protected static ?string $model = ActivityProgress::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?string $recordTitleAttribute = 'Activity Progress';

    protected static string|\UnitEnum|null $navigationGroup = 'Learners';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Activity Progress';

    public static function form(Schema $schema): Schema
    {
        return ActivityProgressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityProgressTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityProgress::route('/'),
            'create' => CreateActivityProgress::route('/create'),
            'edit' => EditActivityProgress::route('/{record}/edit'),
        ];
    }
}
