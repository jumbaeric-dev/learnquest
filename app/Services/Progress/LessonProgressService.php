<?php

namespace App\Services\Progress;

use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\Lesson;
use App\Models\LessonProgress;

class LessonProgressService
{
    /**
     * Recalculate and persist a child's progress for a lesson.
     *
     * This service owns lesson-progress state only.
     *
     * It does NOT:
     * - award XP
     * - update course progress
     * - update skill progress
     * - update streaks
     * - award badges
     * - award achievements
     *
     * Those responsibilities belong to the appropriate
     * orchestration/domain services.
     */
    public function update(
        Child $child,
        Lesson $lesson
    ): LessonProgress {
        $activityIds = $lesson
            ->activities()
            ->pluck('id');

        $totalActivities = $activityIds->count();

        $completedActivities = ActivityProgress::query()
            ->where('child_id', $child->id)
            ->whereIn('activity_id', $activityIds)
            ->where('completed', true)
            ->count();

        $progressPercentage = $totalActivities > 0
            ? round(
                ($completedActivities / $totalActivities) * 100,
                2
            )
            : 0;

        $completed =
            $totalActivities > 0 &&
            $completedActivities === $totalActivities;

        return LessonProgress::updateOrCreate(
            [
                'child_id' => $child->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'completed_activities' => $completedActivities,
                'total_activities' => $totalActivities,
                'progress_percentage' => $progressPercentage,
                'completed' => $completed,
                'completed_at' => $completed
                    ? now()
                    : null,
            ]
        );
    }

    /**
     * Recalculate lesson progress from an activity.
     *
     * Kept as a compatibility wrapper for existing callers.
     *
     * This method does not contain a second implementation of the
     * progress calculation. The canonical implementation is update().
     */
    public function updateFromActivity(
        Child $child,
        $activity
    ): LessonProgress {
        return $this->update(
            $child,
            $activity->lesson
        );
    }

    /**
     * Determine whether the lesson has been completed.
     */
    public function isCompleted(
        Child $child,
        Lesson $lesson
    ): bool {
        return LessonProgress::query()
            ->where('child_id', $child->id)
            ->where('lesson_id', $lesson->id)
            ->value('completed') ?? false;
    }
}