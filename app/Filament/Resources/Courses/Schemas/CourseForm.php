<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Course Information')
                    ->schema([
                        Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, callable $set) =>
                                $set('slug', Str::slug($state))
                            ),

                        TextInput::make('slug')
                            ->required(),

                        Textarea::make('description')
                            ->columnSpanFull(),

                        Select::make('age_group')
                            ->options([
                                '3-5' => 'Tiny Learners (3-5)',
                                '6-9' => 'Explorers (6-9)',
                                '10-14' => 'Achievers (10-14)',
                            ])
                            ->required(),

                        FileUpload::make('thumbnail')
                            ->image()
                            ->directory('courses'),

                        Toggle::make('is_published')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }
}
