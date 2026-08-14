<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class ExplorerHeaderDTO extends DTO
{
    public function __construct(
        public string $name,
        public ?string $avatar,
        public int $level,
        public int $xp,
        public int $nextLevelXp,
        public int $xpPercentage,
        public int $futureReadiness,
        public int $streak,

    ) {}

}
