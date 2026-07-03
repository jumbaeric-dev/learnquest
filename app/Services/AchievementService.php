<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Child;

class AchievementService
{
    public function evaluate(Child $child): array
    {
        $awarded = [];

        $checks = [
            'firstLesson',
            'aiExplorer',
            'promptMaster',
            'aiSafetyChampion',
            'futureInnovator',
            'criticalThinker',
            'problemSolver',
            'learnQuestLegend',
            'streakBadges',
        ];

        foreach ($checks as $check) {
            $result = $this->$check($child);

            if ($result) {
                $awarded = array_merge($awarded, $result);
            }
        }

        return $awarded;
    }

    /**
     * CORE: safe badge awarding (prevents duplicates)
     */
    protected function awardBadge(Child $child, string $badgeSlug): ?Badge
    {
        $badge = Badge::where('slug', $badgeSlug)->first();

        if (! $badge) {
            return null;
        }

        if ($child->badges()->where('badge_id', $badge->id)->exists()) {
            return null;
        }

        $child->badges()->attach($badge->id, [
            'earned_at' => now(),
        ]);

        return $badge;
    }

    // ------------------------------------------------------------
    // ACHIEVEMENT RULES
    // ------------------------------------------------------------

    protected function firstLesson(Child $child): array
    {
        if ($child->lessonProgress()->where('completed', true)->count() >= 1) {
            return array_filter([
                $this->awardBadge($child, 'first-lesson'),
            ]);
        }

        return [];
    }

    protected function aiExplorer(Child $child): array
    {
        if ($child->courseProgress()->where('completed', true)->count() >= 1) {
            return array_filter([
                $this->awardBadge($child, 'ai-explorer'),
            ]);
        }

        return [];
    }

    protected function promptMaster(Child $child): array
    {
        if (
            $child->lessonProgress()
                ->whereHas('lesson', fn($q) =>
                    $q->where('title', 'like', '%prompt%')
                )
                ->where('completed', true)
                ->exists()
        ) {
            return array_filter([
                $this->awardBadge($child, 'prompt-master'),
            ]);
        }

        return [];
    }

    protected function aiSafetyChampion(Child $child): array
    {
        if (
            $child->lessonProgress()
                ->whereHas('lesson', fn($q) =>
                    $q->where('title', 'like', '%safety%')
                )
                ->where('completed', true)
                ->exists()
        ) {
            return array_filter([
                $this->awardBadge($child, 'ai-safety-champion'),
            ]);
        }

        return [];
    }

    protected function futureInnovator(Child $child): array
    {
        if ($child->xp >= 500) {
            return array_filter([
                $this->awardBadge($child, 'future-innovator'),
            ]);
        }

        return [];
    }

    protected function criticalThinker(Child $child): array
    {
        $count = $child->activityProgress()
            ->where('completed', true)
            ->whereHas('activity', fn($q) =>
                $q->where('activity_type', 'quiz')
            )
            ->count();

        if ($count >= 10) {
            return array_filter([
                $this->awardBadge($child, 'critical-thinker'),
            ]);
        }

        return [];
    }

    protected function problemSolver(Child $child): array
    {
        $count = $child->activityProgress()
            ->where('completed', true)
            ->whereHas('activity', fn($q) =>
                $q->where('activity_type', 'project')
            )
            ->count();

        if ($count >= 3) {
            return array_filter([
                $this->awardBadge($child, 'problem-solver'),
            ]);
        }

        return [];
    }

    protected function learnQuestLegend(Child $child): array
    {
        if ($child->badges()->count() >= 10) {
            return array_filter([
                $this->awardBadge($child, 'learnquest-legend'),
            ]);
        }

        return [];
    }

    protected function streakBadges(Child $child): array
    {
        $streak = $child->streak;

        if (! $streak) {
            return [];
        }

        $awarded = [];

        if ($streak->current_streak >= 7) {
            $awarded[] = $this->awardBadge($child, '7-day-streak');
        }

        if ($streak->current_streak >= 30) {
            $awarded[] = $this->awardBadge($child, '30-day-streak');
        }

        if ($streak->current_streak >= 100) {
            $awarded[] = $this->awardBadge($child, '100-day-streak');
        }

        return array_filter($awarded);
    }
}