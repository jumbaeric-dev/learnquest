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
     * Get the child's current course.
     */
    public function getCurrentCourse(Child $child): ?Course
    {
        $progress = CourseProgress::query()
            ->where('child_id', $child->id)
            ->where('completed', false)
            ->orderByDesc('updated_at')
            ->first();

        return $progress?->course()
            ->with([
                'modules.lessons.activities',
            ])
            ->first();
    }

    /**
     * Get the current lesson within the active course.
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
     * Get the next activity the child should complete.
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
