<?php

namespace App\Filament\Resources\ChildStreaks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ChildStreakInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('child_id')
                    ->numeric(),
                TextEntry::make('current_streak')
                    ->numeric(),
                TextEntry::make('longest_streak')
                    ->numeric(),
                TextEntry::make('last_activity_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
