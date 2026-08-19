<?php

namespace App\Services\Progress;

use App\Models\Child;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\LessonProgress;

class CourseProgressService
{
    /**
     * Recalculate and persist a child's progress for a course.
     *
     * This service owns course-progress state only.
     *
     * It does NOT:
     * - award XP
     * - update lesson progress
     * - update skill progress
     * - update streaks
     * - award badges
     * - award achievements
     */
    public function update(
        Child $child,
        Course $course
    ): CourseProgress {
        $lessonIds = $course
            ->modules()
            ->with('lessons')
            ->get()
            ->flatMap(function ($module) {
                return $module->lessons;
            })
            ->pluck('id');

        $totalLessons = $lessonIds->count();

        $completedLessons = $totalLessons > 0
            ? LessonProgress::query()
                ->where('child_id', $child->id)
                ->whereIn('lesson_id', $lessonIds)
                ->where('completed', true)
                ->count()
            : 0;

        $progressPercentage = $totalLessons > 0
            ? round(
                ($completedLessons / $totalLessons) * 100,
                2
            )
            : 0;

        $completed =
            $totalLessons > 0 &&
            $completedLessons === $totalLessons;

        return CourseProgress::updateOrCreate(
            [
                'child_id' => $child->id,
                'course_id' => $course->id,
            ],
            [
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons,
                'progress_percentage' => $progressPercentage,
                'completed' => $completed,
                'completed_at' => $completed
                    ? now()
                    : null,
            ]
        );
    }

    /**
     * Recalculate course progress from a lesson.
     *
     * Compatibility wrapper for existing callers.
     *
     * The canonical calculation lives in update().
     */
    public function updateFromLesson(
        Child $child,
        $lesson
    ): CourseProgress {
        return $this->update(
            $child,
            $lesson->module->course
        );
    }

    /**
     * Determine whether the course has been completed.
     */
    public function isCompleted(
        Child $child,
        Course $course
    ): bool {
        return CourseProgress::query()
            ->where('child_id', $child->id)
            ->where('course_id', $course->id)
            ->value('completed') ?? false;
    }
}