<?php

namespace App\Filament\Resources\LessonProgress\Schemas;

use App\Models\Child;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LessonProgressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Progress Information')
                    ->schema([

                        Select::make('child_id')
                            ->relationship(
                                name: 'child',
                                titleAttribute: 'first_name'
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn(Child $record): string => $record->full_name
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('lesson_id')
                            ->relationship('lesson', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('completed_activities')
                            ->numeric()
                            ->required(),

                        TextInput::make('total_activities')
                            ->numeric()
                            ->required(),

                        TextInput::make('progress_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->required(),

                        Toggle::make('completed'),

                        DateTimePicker::make('completed_at'),

                    ])
                    ->columns(2),

            ]);
    }
}
