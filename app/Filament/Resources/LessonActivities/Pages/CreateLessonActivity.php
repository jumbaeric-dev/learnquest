<?php

namespace App\Filament\Resources\LessonActivities\Pages;

use App\Filament\Resources\LessonActivities\LessonActivityResource;
use App\Services\Learning\PublishedQuizValidator;
use Filament\Resources\Pages\CreateRecord;

class CreateLessonActivity extends CreateRecord
{
    protected static string $resource = LessonActivityResource::class;

    protected function beforeCreate(): void
    {
        app(PublishedQuizValidator::class)->validateForPublication(
            $this->data['activity_type'] ?? null,
            $this->data['is_published'] ?? false,
            $this->data['content'] ?? null,
        );
    }
}
