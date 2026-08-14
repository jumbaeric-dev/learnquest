<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class ContinueLearningDTO extends DTO
{
    public function __construct(
        public string $course,
        public string $lesson,
        public int $progress,
        public string $buttonText,
        public string $buttonAction,
    ) {
    }
}