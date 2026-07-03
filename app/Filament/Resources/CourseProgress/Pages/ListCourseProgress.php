<?php

namespace App\Filament\Resources\CourseProgress\Pages;

use App\Filament\Resources\CourseProgress\CourseProgressResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourseProgress extends ListRecords
{
    protected static string $resource = CourseProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
