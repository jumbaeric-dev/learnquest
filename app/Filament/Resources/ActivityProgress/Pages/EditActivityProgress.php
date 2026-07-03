<?php

namespace App\Filament\Resources\ActivityProgress\Pages;

use App\Filament\Resources\ActivityProgress\ActivityProgressResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditActivityProgress extends EditRecord
{
    protected static string $resource = ActivityProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
