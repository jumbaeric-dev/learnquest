<?php

namespace App\Services\Child\Learning;

use App\Models\Child;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonActivity;
use Symfony\Component\HttpFoundation\Response;

class LearningContentAccessService
{
    public function __construct(
        protected LearningJourneyService $journey,
    ) {}

    /**
     * Ensure the child can browse a published course.
     *
     * Browsing a course is allowed even before the course is selected
     * as the child's active journey. Opening unfinished lessons or
     * activities is restricted by the methods below.
     */
    public function course(Course $course): Course
    {
        $course->loadMissing('subject');

        abort_unless(
            $course->is_published && $course->subject?->is_active,
            Response::HTTP_NOT_FOUND
        );

        return $course->load([
            'subject',
            'modules' => fn ($query) => $query->orderBy('position'),
            'modules.lessons' => fn ($query) => $query
                ->where('is_published', true)
                ->orderBy('position'),
            'modules.lessons.activities' => fn ($query) => $query
                ->where('is_published', true)
                ->orderBy('position'),
        ]);
    }

    /**
     * A child may review a completed lesson at any time. An unfinished
     * lesson must be the next lesson in the child's active course.
     */
    public function lesson(Lesson $lesson, Child $child): Lesson
    {
        $lesson->loadMissing('module.course.subject');

        abort_unless(
            $lesson->is_published
                && $lesson->module?->course?->is_published
                && $lesson->module?->course?->subject?->is_active,
            Response::HTTP_NOT_FOUND
        );

        $isCompleted = $child->lessonProgress()
            ->where('lesson_id', $lesson->id)
            ->where('completed', true)
            ->exists();

        if (! $isCompleted) {
            $currentCourse = $this->journey->getCurrentCourse($child);

            abort_unless(
                $currentCourse !== null
                    && (int) $currentCourse->id === (int) $lesson->module->course_id,
                Response::HTTP_NOT_FOUND
            );

            $currentLesson = $this->journey->getCurrentLesson($child);

            abort_unless(
                $currentLesson !== null
                    && (int) $currentLesson->id === (int) $lesson->id,
                Response::HTTP_NOT_FOUND
            );
        }

        return $lesson->load([
            'module.course',
            'activities' => fn ($query) => $query
                ->where('is_published', true)
                ->orderBy('position'),
        ]);
    }

    /**
     * A child may review a completed activity at any time. An
     * unfinished activity must be the next activity in the child's
     * active lesson and active course.
     */
    public function activity(
        LessonActivity $activity,
        Child $child,
    ): LessonActivity {
        $activity->loadMissing('lesson.module.course.subject');

        abort_unless(
            $activity->is_published
                && $activity->lesson?->is_published
                && $activity->lesson?->module?->course?->is_published
                && $activity->lesson?->module?->course?->subject?->is_active,
            Response::HTTP_NOT_FOUND
        );

        $isCompleted = $child->activityProgress()
            ->where('activity_id', $activity->id)
            ->where('completed', true)
            ->exists();

        if (! $isCompleted) {
            $currentCourse = $this->journey->getCurrentCourse($child);

            abort_unless(
                $currentCourse !== null
                    && (int) $currentCourse->id === (int) $activity->lesson->module->course_id,
                Response::HTTP_NOT_FOUND
            );

            $currentLesson = $this->journey->getCurrentLesson($child);

            abort_unless(
                $currentLesson !== null
                    && (int) $currentLesson->id === (int) $activity->lesson_id,
                Response::HTTP_NOT_FOUND
            );

            $currentActivity = $this->journey->getCurrentActivity($child);

            abort_unless(
                $currentActivity !== null
                    && (int) $currentActivity->id === (int) $activity->id,
                Response::HTTP_NOT_FOUND
            );
        }

        return $activity->load([
            'lesson.module.course',
            'skills',
        ]);
    }
}
