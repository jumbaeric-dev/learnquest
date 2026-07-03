<?php

namespace App\Services\Progress;

use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\Lesson;
use App\Models\LessonProgress;

class LessonProgressService
{
    /**
     * Recalculate lesson progress for a child.
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

        $progress = LessonProgress::updateOrCreate(
            [
                'child_id' => $child->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'completed_activities' => $completedActivities,
                'total_activities' => $totalActivities,
                'progress_percentage' => $progressPercentage,
                'completed' => $completed,
                'completed_at' => $completed ? now() : null,
            ]
        );

        if ($progress->completed && $progress->wasChanged('completed')) {
            $child->addXp($lesson->xp_reward);
        }

        return $progress;
    }
}
