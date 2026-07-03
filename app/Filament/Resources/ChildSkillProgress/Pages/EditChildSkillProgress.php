<?php

namespace App\Filament\Resources\ChildSkillProgress\Pages;

use App\Filament\Resources\ChildSkillProgress\ChildSkillProgressResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditChildSkillProgress extends EditRecord
{
    protected static string $resource = ChildSkillProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
