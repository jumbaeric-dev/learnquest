<?php

namespace App\Filament\Resources\Lessons\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('learning_module_id')
                    ->required()
                    ->numeric(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Section::make('French Translation')
                    ->description('Optional French copy. Empty fields fall back to the canonical English content.')
                    ->schema([
                        TextInput::make('translations.fr.title')->label('Lesson title'),
                        Textarea::make('translations.fr.description')->label('Lesson description')->columnSpanFull(),
                    ])
                    ->columns(2),
                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('xp_reward')
                    ->required()
                    ->numeric()
                    ->default(10),
                TextInput::make('estimated_minutes')
                    ->numeric(),
                Toggle::make('is_published')
                    ->required(),
            ]);
    }
}
