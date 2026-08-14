<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class LearningWorldDTO extends DTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $icon,
        public int $courses,
    ) {
    }

}