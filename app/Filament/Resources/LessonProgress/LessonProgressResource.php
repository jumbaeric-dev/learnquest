<?php

namespace App\Filament\Resources\LessonProgress;

use App\Filament\Resources\LessonProgress\Pages\CreateLessonProgress;
use App\Filament\Resources\LessonProgress\Pages\EditLessonProgress;
use App\Filament\Resources\LessonProgress\Pages\ListLessonProgress;
use App\Filament\Resources\LessonProgress\Schemas\LessonProgressForm;
use App\Filament\Resources\LessonProgress\Tables\LessonProgressTable;
use App\Models\LessonProgress;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LessonProgressResource extends Resource
{
    protected static ?string $model = LessonProgress::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $recordTitleAttribute = 'Lesson Progress';

    protected static string|\UnitEnum|null $navigationGroup = 'Learners';

    protected static ?string $navigationLabel = 'Lesson Progress';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return LessonProgressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LessonProgressTable::configure($table);
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
            'index' => ListLessonProgress::route('/'),
            'create' => CreateLessonProgress::route('/create'),
            'edit' => EditLessonProgress::route('/{record}/edit'),
        ];
    }
}
