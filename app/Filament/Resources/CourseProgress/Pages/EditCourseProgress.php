<?php

namespace App\Filament\Resources\CourseProgress\Pages;

use App\Filament\Resources\CourseProgress\CourseProgressResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourseProgress extends EditRecord
{
    protected static string $resource = CourseProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
