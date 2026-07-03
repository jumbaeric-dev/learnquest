<?php

namespace App\Filament\Resources\ActivityProgress\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Child;

class ActivityProgressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Progress Information')
                    ->schema([

                        Select::make('child_id')
                            ->label('Child')
                            ->options(
                                Child::query()
                                    ->get()
                                    ->mapWithKeys(fn($child) => [
                                        $child->id => $child->first_name . ' ' . $child->last_name
                                    ])
                            )
                            ->searchable()
                            ->required(),

                        Select::make('activity_id')
                            ->relationship('activity', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Toggle::make('completed')
                            ->default(false),

                        TextInput::make('score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),

                        TextInput::make('xp_earned')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        DateTimePicker::make('completed_at'),

                    ])
                    ->columns(2),

            ]);
    }
}
