<?php

namespace App\Livewire\Child\Learning;

use App\Models\Course as CourseModel;
use App\Models\CourseProgress;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\LearningContentAccessService;
use Illuminate\Support\Collection;
use Livewire\Component;

class Course extends Component
{
  public CourseModel $course;

  public ?CourseProgress $progress = null;

  /**
   * IDs of lessons completed by the current child.
   *
   * @var Collection<int, int>
   */
  public Collection $completedLessonIds;

  public function mount(
    CourseModel $course,
    LearningContentAccessService $access,
    CurrentChildService $currentChildService
  ): void {
    $this->course = $access->course($course);

    $child = $currentChildService->current();

    abort_unless($child, 403);

    $this->progress = $child
      ->courseProgress()
      ->where("course_id", $this->course->id)
      ->first();

    $lessonIds = $this->course->modules
      ->flatMap(fn($module) => $module->lessons)
      ->pluck("id");

    $this->completedLessonIds = $child
      ->lessonProgress()
      ->whereIn("lesson_id", $lessonIds)
      ->where("completed", true)
      ->pluck("lesson_id");
  }

  public function getProgressPercentageProperty(): int
  {
    return (int) round($this->progress?->progress_percentage ?? 0);
  }

  public function getCompletedLessonsProperty(): int
  {
    return (int) $this->completedLessonIds->count();
  }

  public function getTotalLessonsProperty(): int
  {
    return $this->course->modules
      ->flatMap(fn($module) => $module->lessons)
      ->count();
  }

  public function isLessonCompleted(int $lessonId): bool
  {
    return $this->completedLessonIds->contains($lessonId);
  }

  public function render()
  {
    return view("livewire.child.learning.course");
  }
}
