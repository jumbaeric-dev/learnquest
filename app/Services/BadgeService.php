<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Child;

class BadgeService
{
    public function __construct(
        protected LevelService $levelService,
        protected StreakService $streakService
    ) {}

    /**
     * Evaluate and award all eligible badges for a child.
     */
    public function evaluate(Child $child): array
    {
        $awarded = [];

        $badges = Badge::where('is_active', true)->get();

        foreach ($badges as $badge) {

            if ($this->alreadyHasBadge($child, $badge)) {
                continue;
            }

            if ($this->isEligible($child, $badge)) {
                $this->award($child, $badge);

                $awarded[] = $badge;
            }
        }

        return $awarded;
    }

    /**
     * Determine if child already has badge.
     */
    protected function alreadyHasBadge(Child $child, Badge $badge): bool
    {
        return $child->badges()
            ->where('badge_id', $badge->id)
            ->exists();
    }

    /**
     * Core rule evaluation.
     */
    protected function isEligible(Child $child, Badge $badge): bool
    {
        return match (true) {

            // XP-based badges: xp-100, xp-500, etc.
            str_starts_with($badge->slug, 'xp-') =>
                $child->xp >= (int) str_replace('xp-', '', $badge->slug),

            // Level-based badges: level-5, level-10
            str_starts_with($badge->slug, 'level-') =>
                $child->level >= (int) str_replace('level-', '', $badge->slug),

            // Streak-based badges: streak-3, streak-7
            str_starts_with($badge->slug, 'streak-') =>
                $this->streakService->current($child) >= (int) str_replace('streak-', '', $badge->slug),

            default => false,
        };
    }

    /**
     * Award badge to child.
     */
    protected function award(Child $child, Badge $badge): void
    {
        $child->badges()->attach($badge->id, [
            'earned_at' => now(),
        ]);
    }

    /**
     * Get all badges a child has earned.
     */
    public function earned(Child $child)
    {
        return $child->badges;
    }

    /**
     * Check if child has a specific badge.
     */
    public function has(Child $child, string $slug): bool
    {
        return $child->badges()
            ->where('slug', $slug)
            ->exists();
    }
}