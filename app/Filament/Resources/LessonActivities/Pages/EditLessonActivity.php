<?php

namespace App\Filament\Resources\LessonActivities\Pages;

use App\Filament\Resources\LessonActivities\LessonActivityResource;
use App\Services\Learning\PublishedQuizValidator;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLessonActivity extends EditRecord
{
    protected static string $resource = LessonActivityResource::class;

    protected function beforeSave(): void
    {
        app(PublishedQuizValidator::class)->validateForPublication(
            $this->data['activity_type'] ?? null,
            $this->data['is_published'] ?? false,
            $this->data['content'] ?? null,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
