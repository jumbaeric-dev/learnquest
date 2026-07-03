<?php

namespace App\Filament\Resources\Children\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChildForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Child Information')
                    ->schema([

                        Select::make('parent_id')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->maxLength(255),

                        DatePicker::make('date_of_birth'),

                        FileUpload::make('avatar')
                            ->image()
                            ->directory('children'),

                    ])
                    ->columns(2),

                Section::make('Progress')
                    ->schema([

                        TextInput::make('xp')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('level')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Toggle::make('is_active')
                            ->default(true),

                    ])
                    ->columns(3),

            ]);
    }
}