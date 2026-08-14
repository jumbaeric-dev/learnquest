<?php

namespace App\Data\Child\World;

use App\Data\DTO;

final readonly class WorldHeroDTO extends DTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $tagline,
        public ?string $description,
        public ?string $storyIntro,
        public ?string $heroCharacter,
        public ?string $icon,
        public ?string $coverImage,
        public string $themeColor,
        public string $difficulty,
        public int $courses,
    ) {}
}