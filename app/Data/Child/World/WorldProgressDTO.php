<?php

namespace App\Data\Child\World;

use App\Data\DTO;

final readonly class WorldProgressDTO extends DTO
{
    public function __construct(
        public int $completedCourses,
        public int $totalCourses,
        public int $percentage,
    ) {}
}