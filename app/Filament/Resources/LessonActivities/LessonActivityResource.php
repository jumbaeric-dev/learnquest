<?php

namespace App\Filament\Resources\LessonActivities;

use App\Filament\Resources\LessonActivities\Pages\CreateLessonActivity;
use App\Filament\Resources\LessonActivities\Pages\EditLessonActivity;
use App\Filament\Resources\LessonActivities\Pages\ListLessonActivities;
use App\Filament\Resources\LessonActivities\Schemas\LessonActivityForm;
use App\Filament\Resources\LessonActivities\Tables\LessonActivitiesTable;
use App\Models\LessonActivity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LessonActivityResource extends Resource
{
    protected static ?string $model = LessonActivity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bolt;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Curriculum';

    protected static ?string $navigationLabel = 'Lesson Activities';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return LessonActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LessonActivitiesTable::configure($table);
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
            'index' => ListLessonActivities::route('/'),
            'create' => CreateLessonActivity::route('/create'),
            'edit' => EditLessonActivity::route('/{record}/edit'),
        ];
    }
}