<?php

namespace App\Data\Child\World;

use App\Data\DTO;

final readonly class ContinueJourneyDTO extends DTO
{
    public function __construct(
        public string $course,
        public string $lesson,
        public int $progress,
        public string $buttonText,
        public string $buttonAction,
    ) {}
}