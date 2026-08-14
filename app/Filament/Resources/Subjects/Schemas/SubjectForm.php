<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Basic Information')
                    ->schema([

                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, callable $set) => $set(
                                    'slug',
                                    Str::slug($state)
                                )
                            ),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('tagline')
                            ->maxLength(255),

                    ])
                    ->columns(2),

                Section::make('Visual Identity')
                    ->schema([

                        TextInput::make('icon')
                            ->helperText('Heroicon, Lucide or Emoji'),

                        FileUpload::make('cover_image')
                            ->directory('worlds')
                            ->image(),

                        TextInput::make('banner_animation')
                            ->helperText('Lottie JSON filename or animation asset'),

                        ColorPicker::make('theme_color'),

                    ])
                    ->columns(2),

                Section::make('Story & Character')
                    ->schema([

                        Textarea::make('story_intro')
                            ->rows(5)
                            ->columnSpanFull(),

                        TextInput::make('hero_character'),

                        TextInput::make('background_music')
                            ->helperText('Future feature'),

                    ])
                    ->columns(2),

                Section::make('Progression')
                    ->schema([

                        Radio::make('difficulty')
                            ->options([
                                'beginner' => '🟢 Beginner',
                                'intermediate' => '🟡 Intermediate',
                                'advanced' => '🔴 Advanced',
                            ])
                            ->inline()
                            ->default('beginner')
                            ->required(),

                        TextInput::make('unlock_level')
                            ->numeric()
                            ->default(1),

                        TextInput::make('estimated_hours')
                            ->numeric()
                            ->suffix('hrs'),

                        TextInput::make('sort_order')
                            ->numeric(),

                        TextInput::make('badge_icon')
                            ->helperText('Heroicon, Lucide or Emoji'),

                    ])
                    ->columns(3),

                Section::make('Discovery & Publishing')
                    ->schema([

                        Toggle::make('is_featured')
                            ->default(false),

                        Toggle::make('is_seasonal')
                            ->default(false),

                        Toggle::make('is_active')
                            ->default(true),

                    ])
                    ->columns(3),

            ]);
    }
}
