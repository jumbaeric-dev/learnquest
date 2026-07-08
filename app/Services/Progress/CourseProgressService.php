<?php

namespace App\Services\Progress;

use App\Models\Child;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\LessonProgress;

class CourseProgressService
{
    /**
     * Recalculate course progress for a child.
     */
    public function update(
        Child $child,
        Course $course
    ): CourseProgress {

        // Get every lesson belonging to this course
        $lessonIds = $course
            ->modules()
            ->with('lessons')
            ->get()
            ->flatMap(function ($module) {
                return $module->lessons;
            })
            ->pluck('id');

        $totalLessons = $lessonIds->count();

        $completedLessons = LessonProgress::query()
            ->where('child_id', $child->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('completed', true)
            ->count();

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
     * Determine whether a course has been completed.
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

    public function updateFromLesson(Child $child, $lesson)
    {
        $course = $lesson->module->course;

        $total = $course->modules()
            ->with('lessons')
            ->get()
            ->pluck('lessons')
            ->flatten()
            ->count();

        $completed = $child->lessonProgress()
            ->whereHas('lesson', function ($q) use ($course) {
                $q->whereHas('module', function ($q2) use ($course) {
                    $q2->where('course_id', $course->id);
                });
            })
            ->where('completed', true)
            ->count();

        $percentage = $total > 0
            ? ($completed / $total) * 100
            : 0;

        return $child->courseProgress()->updateOrCreate(
            [
                'course_id' => $course->id,
            ],
            [
                'completed_lessons' => $completed,
                'total_lessons' => $total,
                'progress_percentage' => $percentage,
                'completed' => $percentage >= 100,
                'completed_at' => $percentage >= 100 ? now() : null,
            ]
        );
    }
}
