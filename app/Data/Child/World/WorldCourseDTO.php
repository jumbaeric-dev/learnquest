<?php

namespace App\Data\Child\World;

use App\Data\DTO;

final readonly class WorldCourseDTO extends DTO
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public ?string $icon,
        public string $ageGroup,
        public bool $locked,
        public int $progress,
    ) {}
}