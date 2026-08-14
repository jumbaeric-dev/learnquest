<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class DailyChallengeDTO extends DTO
{
    public function __construct(
        public string $title,
        public string $description,
        public int $current,
        public int $target,
        public int $xpReward,
        public string $buttonText,
    ) {
    }
}