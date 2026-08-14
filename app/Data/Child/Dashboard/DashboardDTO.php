<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class DashboardDTO extends DTO
{
    public function __construct(
        public ExplorerHeaderDTO $header,
        public WelcomeDTO $welcome,
        public ?CurrentMissionDTO $mission,
        public array $continueLearning,
        public array $dailyChallenge,
        public array $recommended,
        public array $worlds,
        public array $skills,
        public array $achievements,
        public array $community,
    ) {
    }

    // public function toArray(): array
    // {
    //     return [

    //         'header' => $this->header->toArray(),

    //         'welcome' => $this->welcome->toArray(),

    //         'mission' => $this->mission?->toArray(),

    //         'continueLearning' => $this->continueLearning,

    //         'dailyChallenge' => $this->dailyChallenge,

    //         'recommended' => $this->recommended,

    //         'worlds' => $this->worlds,

    //         'skills' => $this->skills,

    //         'achievements' => $this->achievements,

    //         'community' => $this->community,

    //     ];
    // }
}