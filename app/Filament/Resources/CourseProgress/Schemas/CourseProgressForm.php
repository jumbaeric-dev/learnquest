<?php

namespace App\Filament\Resources\CourseProgress\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseProgressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('child_id')
                    ->required()
                    ->numeric(),
                TextInput::make('course_id')
                    ->required()
                    ->numeric(),
                TextInput::make('completed_lessons')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_lessons')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('progress_percentage')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('completed')
                    ->required(),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
