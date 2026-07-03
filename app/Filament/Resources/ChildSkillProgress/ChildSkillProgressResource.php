<?php

namespace App\Filament\Resources\ChildSkillProgress;

use App\Filament\Resources\ChildSkillProgress\Pages\ListChildSkillProgress;
use App\Filament\Resources\ChildSkillProgress\Schemas\ChildSkillProgressForm;
use App\Filament\Resources\ChildSkillProgress\Tables\ChildSkillProgressTable;
use App\Models\ChildSkillProgress;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChildSkillProgressResource extends Resource
{
    protected static ?string $model = ChildSkillProgress::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup =
        'Learners';

    protected static ?string $navigationLabel =
        'Skill Progress';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return ChildSkillProgressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChildSkillProgressTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChildSkillProgress::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}