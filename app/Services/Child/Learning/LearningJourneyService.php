<?php

namespace App\Services\Child\Learning;

use App\Models\Child;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\Lesson;
use App\Models\LessonActivity;

class LearningJourneyService
{
    /**
     * Get the child's current published course.
     *
     * A child can only continue learning from:
     * - an active World
     * - a published Course
     *
     * Unpublished courses are ignored even if progress exists for them.
     */
    public function getCurrentCourse(Child $child): ?Course
    {
        $progress = CourseProgress::query()
            ->where('child_id', $child->id)
            ->where('completed', false)
            ->whereHas('course', function ($query) {
                $query
                    ->where('is_published', true)
                    ->whereHas('subject', function ($query) {
                        $query->where('is_active', true);
                    });
            })
            ->orderByDesc('updated_at')
            ->first();

        if (! $progress) {
            return null;
        }

        return $progress->course()
            ->where('is_published', true)
            ->whereHas('subject', function ($query) {
                $query->where('is_active', true);
            })
            ->with([
                'modules.lessons' => function ($query) {
                    $query
                        ->where('is_published', true)
                        ->orderBy('position');
                },

                'modules.lessons.activities' => function ($query) {
                    $query
                        ->where('is_published', true)
                        ->orderBy('position');
                },
            ])
            ->first();
    }

    /**
     * Get the current published lesson within the active course.
     *
     * Unpublished lessons are never presented as the child's
     * next lesson.
     */
    public function getCurrentLesson(Child $child): ?Lesson
    {
        $course = $this->getCurrentCourse($child);

        if (! $course) {
            return null;
        }

        foreach ($course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                $progress = $child
                    ->lessonProgress()
                    ->where('lesson_id', $lesson->id)
                    ->first();

                if (! $progress || ! $progress->completed) {
                    return $lesson;
                }
            }
        }

        return null;
    }

    /**
     * Get the next published activity the child should complete.
     *
     * Unpublished activities are excluded from the course journey
     * before this method evaluates progress.
     */
    public function getCurrentActivity(Child $child): ?LessonActivity
    {
        $lesson = $this->getCurrentLesson($child);

        if (! $lesson) {
            return null;
        }

        foreach ($lesson->activities as $activity) {
            $progress = $child
                ->activityProgress()
                ->where('activity_id', $activity->id)
                ->first();

            if (! $progress || ! $progress->completed) {
                return $activity;
            }
        }

        return null;
    }

    /**
     * Dashboard mission payload.
     */
    public function getCurrentMission(Child $child): ?array
    {
        $course = $this->getCurrentCourse($child);

        $lesson = $this->getCurrentLesson($child);

        $activity = $this->getCurrentActivity($child);

        if (! $course || ! $lesson || ! $activity) {
            return null;
        }

        return [
            'course' => $course,
            'lesson' => $lesson,
            'activity' => $activity,
            'xp' => $activity->xp_reward,
            'minutes' => $lesson->estimated_minutes ?? 5,
            'progress' => $this->getCourseProgressPercentage(
                $child,
                $course
            ),
        ];
    }

    /**
     * Current course completion.
     */
    public function getCourseProgressPercentage(
        Child $child,
        Course $course
    ): int {
        $progress = $child
            ->courseProgress()
            ->where('course_id', $course->id)
            ->first();

        return $progress?->progress_percentage ?? 0;
    }
}