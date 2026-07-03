<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Child;

class AchievementService
{
    public static function evaluate(Child $child): void
    {
        self::checkFirstLesson($child);

        self::checkAiExplorer($child);

        self::checkPromptMaster($child);

        self::checkAiSafetyChampion($child);

        self::checkFutureInnovator($child);

        self::checkCriticalThinker($child);

        self::checkProblemSolver($child);

        self::checkLearnQuestLegend($child);

        self::checkStreakBadges($child);
    }

    protected static function awardBadge(
        Child $child,
        string $badgeSlug
    ): void {
        $badge = Badge::where('slug', $badgeSlug)->first();

        if (! $badge) {
            return;
        }

        $child->awardBadge($badge);
    }

    protected static function checkFirstLesson(
        Child $child
    ): void {
        if (
            $child->lessonProgress()
            ->where('completed', true)
            ->count() >= 1
        ) {
            self::awardBadge(
                $child,
                'first-lesson'
            );
        }
    }

    protected static function checkAiExplorer(
        Child $child
    ): void {
        if (
            $child->courseProgress()
            ->where('completed', true)
            ->count() >= 1
        ) {
            self::awardBadge(
                $child,
                'ai-explorer'
            );
        }
    }

    protected static function checkPromptMaster(
        Child $child
    ): void {

        $completed = $child
            ->lessonProgress()
            ->whereHas(
                'lesson',
                fn($q) =>
                $q->where(
                    'title',
                    'like',
                    '%prompt%'
                )
            )
            ->where('completed', true)
            ->exists();

        if ($completed) {
            self::awardBadge(
                $child,
                'prompt-master'
            );
        }
    }

    protected static function checkAiSafetyChampion(
        Child $child
    ): void {

        $completed = $child
            ->lessonProgress()
            ->whereHas(
                'lesson',
                fn($q) =>
                $q->where(
                    'title',
                    'like',
                    '%safety%'
                )
            )
            ->where('completed', true)
            ->exists();

        if ($completed) {
            self::awardBadge(
                $child,
                'ai-safety-champion'
            );
        }
    }

    protected static function checkFutureInnovator(
        Child $child
    ): void {
        if ($child->xp >= 500) {

            self::awardBadge(
                $child,
                'future-innovator'
            );
        }
    }

    protected static function checkCriticalThinker(
        Child $child
    ): void {

        $completedQuizzes = $child
            ->activityProgress()
            ->where('completed', true)
            ->whereHas(
                'activity',
                fn($q) =>
                $q->where(
                    'activity_type',
                    'quiz'
                )
            )
            ->count();

        if ($completedQuizzes >= 10) {

            self::awardBadge(
                $child,
                'critical-thinker'
            );
        }
    }

    protected static function checkProblemSolver(
        Child $child
    ): void {

        $completedProjects = $child
            ->activityProgress()
            ->where('completed', true)
            ->whereHas(
                'activity',
                fn($q) =>
                $q->where(
                    'activity_type',
                    'project'
                )
            )
            ->count();

        if ($completedProjects >= 3) {

            self::awardBadge(
                $child,
                'problem-solver'
            );
        }
    }

    protected static function checkLearnQuestLegend(
        Child $child
    ): void {

        if (
            $child->badges()
            ->count() >= 10
        ) {
            self::awardBadge(
                $child,
                'learnquest-legend'
            );
        }
    }

    protected static function checkStreakBadges(
        Child $child
    ): void {

        $streak = $child->streak;

        if (! $streak) {
            return;
        }

        if ($streak->current_streak >= 7) {
            self::awardBadge(
                $child,
                '7-day-streak'
            );
        }

        if ($streak->current_streak >= 30) {
            self::awardBadge(
                $child,
                '30-day-streak'
            );
        }

        if ($streak->current_streak >= 100) {
            self::awardBadge(
                $child,
                '100-day-streak'
            );
        }
    }
}
