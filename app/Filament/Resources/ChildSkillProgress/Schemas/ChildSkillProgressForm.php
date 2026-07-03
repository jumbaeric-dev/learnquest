<?php

namespace App\Filament\Resources\ChildSkillProgress\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChildSkillProgressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Skill Progress')
                    ->schema([

                        Select::make('child_id')
                            ->relationship(
                                name: 'child',
                                modifyQueryUsing: fn($query) => $query
                                    ->orderBy('first_name')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => $record->full_name
                            )
                            ->disabled(),

                        Select::make('skill_id')
                            ->relationship('skill', 'name')
                            ->disabled(),

                        TextInput::make('xp')
                            ->disabled(),

                        TextInput::make('level')
                            ->disabled(),

                        TextInput::make('progress_percentage')
                            ->suffix('%')
                            ->disabled(),

                    ])
                    ->columns(2),

            ]);
    }
}
