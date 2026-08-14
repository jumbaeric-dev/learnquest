<?php

namespace App\Data\Child\World;

use App\Data\Child\World\WorldHeroDTO;
use App\Data\Child\World\WorldProgressDTO;
use App\Data\DTO;


final readonly class WorldDTO extends DTO
{
    /**
     * @param WorldCourseDTO[] $courses
     */
    public function __construct(
        public WorldHeroDTO $hero,
        public ?ContinueJourneyDTO $journey,
        public WorldProgressDTO $progress,
        public array $courses,
    ) {}
}