<?php

namespace App\Services\Child\Learning;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonActivity;
use Symfony\Component\HttpFoundation\Response;

class LearningContentAccessService
{
  /**
   * Ensure the child can access the course.
   *
   * Unpublished curriculum is intentionally treated as
   * non-existent to the child.
   */
  public function course(Course $course): Course
  {
    abort_unless($course->is_published, Response::HTTP_NOT_FOUND);

    return $course->load([
      "subject",
      "modules" => fn($query) => $query->orderBy("position"),

      "modules.lessons" => fn($query) => $query
        ->where("is_published", true)
        ->orderBy("position"),

      "modules.lessons.activities" => fn($query) => $query
        ->where("is_published", true)
        ->orderBy("position"),
    ]);
  }
  /**
   * Ensure the child can access the lesson.
   *
   * Both the lesson and its parent course must be published.
   */
  public function lesson(Lesson $lesson): Lesson
  {
    $lesson->loadMissing("module.course");

    abort_unless(
      $lesson->is_published && $lesson->module?->course?->is_published,
      Response::HTTP_NOT_FOUND
    );

    return $lesson->load([
      "module.course",
      "activities" => fn($query) => $query
        ->where("is_published", true)
        ->orderBy("position"),
    ]);
  }

  /**
   * Ensure the child can access the activity.
   *
   * The activity, lesson, and parent course must all be published.
   */
  public function activity(LessonActivity $activity): LessonActivity
  {
    $activity->loadMissing("lesson.module.course");

    abort_unless(
      $activity->is_published &&
        $activity->lesson?->is_published &&
        $activity->lesson?->module?->course?->is_published,
      Response::HTTP_NOT_FOUND
    );

    return $activity->load(["lesson.module.course", "skills"]);
  }
}
