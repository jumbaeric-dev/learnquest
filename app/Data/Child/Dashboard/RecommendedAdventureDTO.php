<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class RecommendedAdventureDTO extends DTO
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public ?string $icon,
        public string $ageGroup,
    ) {
    }

}