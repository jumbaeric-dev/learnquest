<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class SkillPreviewDTO extends DTO
{
    public function __construct(
        public string $name,
        public ?string $icon,
        public int $xp,
        public int $progress,
    ) {
    }

}