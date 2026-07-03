<?php

namespace App\Filament\Resources\ChildSkillProgress\Pages;

use App\Filament\Resources\ChildSkillProgress\ChildSkillProgressResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChildSkillProgress extends ViewRecord
{
    protected static string $resource = ChildSkillProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
