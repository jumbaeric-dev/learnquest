<?php

namespace App\Filament\Resources\ChildStreaks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChildStreakForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Streak Information')
                    ->schema([

                        Select::make('child_id')
                            ->relationship('child', 'full_name')
                            ->disabled(),

                        TextInput::make('current_streak')
                            ->disabled(),

                        TextInput::make('longest_streak')
                            ->disabled(),

                        DatePicker::make('last_activity_date')
                            ->disabled(),

                    ])
                    ->columns(2),

            ]);
    }
}