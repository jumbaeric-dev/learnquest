<?php

namespace App\Filament\Resources\CourseModules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Module Information')
                    ->schema([
                        Select::make('course_id')
                            ->relationship('course', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('title')
                            ->required(),

                        Textarea::make('description'),

                        TextInput::make('position')
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('French Translation')
                    ->description('Optional French copy. Empty fields fall back to the canonical English content.')
                    ->schema([
                        TextInput::make('translations.fr.title')->label('Module title'),
                        Textarea::make('translations.fr.description')->label('Module description'),
                    ])
                    ->columns(2),
            ]);
    }
}
