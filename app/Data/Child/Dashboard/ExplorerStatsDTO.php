<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class ExplorerStatsDTO extends DTO
{
    public function __construct(
        public int $level,
        public int $xp,
        public int $streak,
    ) {
    }

}