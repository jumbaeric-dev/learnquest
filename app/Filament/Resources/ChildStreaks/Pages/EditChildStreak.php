<?php

namespace App\Filament\Resources\ChildStreaks\Pages;

use App\Filament\Resources\ChildStreaks\ChildStreakResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditChildStreak extends EditRecord
{
    protected static string $resource = ChildStreakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
