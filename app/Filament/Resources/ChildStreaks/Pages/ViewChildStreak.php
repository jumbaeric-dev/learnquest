<?php

namespace App\Filament\Resources\ChildStreaks\Pages;

use App\Filament\Resources\ChildStreaks\ChildStreakResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChildStreak extends ViewRecord
{
    protected static string $resource = ChildStreakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
