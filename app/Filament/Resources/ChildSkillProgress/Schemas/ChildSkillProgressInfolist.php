<?php

namespace App\Filament\Resources\ChildSkillProgress\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ChildSkillProgressInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('child_id')
                    ->numeric(),
                TextEntry::make('skill_id')
                    ->numeric(),
                TextEntry::make('xp')
                    ->numeric(),
                TextEntry::make('level')
                    ->numeric(),
                TextEntry::make('progress_percentage')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
