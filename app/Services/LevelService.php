<?php

namespace App\Services;

use App\Models\Child;

class LevelService
{
    /**
     * Calculate the level from total XP.
     */
    public function calculateLevel(int $xp): int
    {
        $levels = config('learnquest.levels');

        $currentLevel = 1;

        foreach ($levels as $level => $requiredXp) {
            if ($xp >= $requiredXp) {
                $currentLevel = $level;
            } else {
                break;
            }
        }

        return $currentLevel;
    }

    /**
     * Update the child's level.
     *
     * Returns:
     * - child
     * - old_level
     * - new_level
     * - leveled_up
     */
    public function update(Child $child): array
    {
        $oldLevel = $child->level;

        $newLevel = $this->calculateLevel($child->xp);

        if ($newLevel !== $oldLevel) {
            $child->update([
                'level' => $newLevel,
            ]);
        }

        return [
            'child' => $child->refresh(),
            'old_level' => $oldLevel,
            'new_level' => $newLevel,
            'leveled_up' => $newLevel > $oldLevel,
        ];
    }

    /**
     * Get XP required for the next level.
     */
    public function nextLevelXp(Child $child): ?int
    {
        $levels = config('learnquest.levels');

        foreach ($levels as $level => $requiredXp) {
            if ($level > $child->level) {
                return $requiredXp;
            }
        }

        return null;
    }

    /**
     * XP remaining until the next level.
     */
    public function xpRemaining(Child $child): int
    {
        $next = $this->nextLevelXp($child);

        if ($next === null) {
            return 0;
        }

        return max(0, $next - $child->xp);
    }

    /**
     * Is the child at the maximum configured level?
     */
    public function isMaxLevel(Child $child): bool
    {
        return $child->level >= max(array_keys(config('learnquest.levels')));
    }

    /**
     * Get progress percentage toward the next level.
     */
    public function progressToNextLevel(Child $child): float
    {
        if ($this->isMaxLevel($child)) {
            return 100;
        }

        $levels = config('learnquest.levels');

        $currentLevelXp = $levels[$child->level];

        $nextLevelXp = $this->nextLevelXp($child);

        $earned = $child->xp - $currentLevelXp;

        $required = $nextLevelXp - $currentLevelXp;

        if ($required <= 0) {
            return 100;
        }

        return round(
            ($earned / $required) * 100,
            2
        );
    }
}