<?php

namespace App\Filament\Resources\ActivityProgress\Pages;

use App\Filament\Resources\ActivityProgress\ActivityProgressResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActivityProgress extends ListRecords
{
    protected static string $resource = ActivityProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
