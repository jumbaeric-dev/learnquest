<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class CurrentMissionDTO extends DTO
{
    public function __construct(
        public string $course,
        public string $lesson,
        public string $activity,
        public int $progress,
        public int $xpReward,
        public int $estimatedMinutes,
        public string $buttonText,
        public string $buttonAction,
    ) {
    }

}