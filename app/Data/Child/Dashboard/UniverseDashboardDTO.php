<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class UniverseDashboardDTO extends DTO
{
    /**
     * @param SkillPreviewDTO[] $skills
     * @param AchievementDTO[] $achievements
     * @param LearningWorldDTO[] $worlds
     * @param RecommendedAdventureDTO[] $recommendations
     */
    public function __construct(
        public ExplorerHeaderDTO $explorer,
        public WelcomeDTO $welcome,
        public NovaWidgetDTO $nova,
        public ?CurrentMissionDTO $mission,
        public ?ContinueLearningDTO $learning,
        public array $skills,
        public array $achievements,
        public array $worlds,
        public array $recommendations,
        public DailyChallengeDTO $dailyChallenge,
        public CommunityPreviewDTO $community,
    ) {}
}