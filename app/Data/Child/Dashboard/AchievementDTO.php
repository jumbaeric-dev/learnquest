<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class AchievementDTO extends DTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?string $icon,
        public ?string $earnedAt,

    ) {
    }

}