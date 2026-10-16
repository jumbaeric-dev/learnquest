<?php

namespace App\Livewire\Child\Learning;

use App\Livewire\Child\Learning\Concerns\InteractsWithLearningShell;
use App\Models\Lesson as LessonModel;
use App\Models\LessonProgress;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\LearningContentAccessService;
use Illuminate\Support\Collection;
use Livewire\Component;

class Lesson extends Component
{
  use InteractsWithLearningShell;

  public LessonModel $lesson;

  public ?LessonProgress $progress = null;

  /**
   * IDs of activities completed by the current child.
   *
   * @var Collection<int, int>
   */
  public Collection $completedActivityIds;

  public function mount(
    LessonModel $lesson,
    LearningContentAccessService $access,
    CurrentChildService $currentChildService
  ): void {
    $this->lesson = $access->lesson($lesson);

    $this->lesson->loadMissing("module.course.subject");

    $child = $currentChildService->current();

    abort_unless($child, 403);

    $this->prepareShell(
      $child,
      $this->lesson->module->course->subject?->slug
    );

    $this->progress = $child
      ->lessonProgress()
      ->where("lesson_id", $this->lesson->id)
      ->first();

    $this->completedActivityIds = $child
      ->activityProgress()
      ->whereIn("activity_id", $this->lesson->activities->pluck("id"))
      ->where("completed", true)
      ->pluck("activity_id");
  }

  public function getProgressPercentageProperty(): int
  {
    return (int) round($this->progress?->progress_percentage ?? 0);
  }

  public function getCompletedActivitiesProperty(): int
  {
    return (int) ($this->progress?->completed_activities ?? 0);
  }

  public function getTotalActivitiesProperty(): int
  {
    return (int) ($this->progress?->total_activities ??
      $this->lesson->activities->count());
  }

  public function getIsCompletedProperty(): bool
  {
    return (bool) ($this->progress?->completed ?? false);
  }

  public function isActivityCompleted(int $activityId): bool
  {
    return $this->completedActivityIds->contains($activityId);
  }

  public function render()
  {
    return view("livewire.child.learning.lesson");
  }
}
