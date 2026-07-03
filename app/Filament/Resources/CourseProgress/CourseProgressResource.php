<?php

namespace App\Filament\Resources\CourseProgress;

use App\Filament\Resources\CourseProgress\Pages\CreateCourseProgress;
use App\Filament\Resources\CourseProgress\Pages\EditCourseProgress;
use App\Filament\Resources\CourseProgress\Pages\ListCourseProgress;
use App\Filament\Resources\CourseProgress\Schemas\CourseProgressForm;
use App\Filament\Resources\CourseProgress\Tables\CourseProgressTable;
use App\Models\CourseProgress;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CourseProgressResource extends Resource
{
    protected static ?string $model = CourseProgress::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare    ;

    protected static ?string $recordTitleAttribute = 'Course Progress';

    protected static string|\UnitEnum|null $navigationGroup = 'Learners';

    protected static ?string $navigationLabel = 'Course Progress';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return CourseProgressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseProgressTable::configure($table);
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
            'index' => ListCourseProgress::route('/'),
            'create' => CreateCourseProgress::route('/create'),
            'edit' => EditCourseProgress::route('/{record}/edit'),
        ];
    }
}
