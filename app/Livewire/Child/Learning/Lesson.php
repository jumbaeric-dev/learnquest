<?php

namespace App\Livewire\Child\Learning;

use App\Models\Lesson as LessonModel;
use App\Models\LessonProgress;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\LearningContentAccessService;
use Livewire\Component;

class Lesson extends Component
{
  public LessonModel $lesson;

  public ?LessonProgress $progress = null;

  public function mount(
    LessonModel $lesson,
    LearningContentAccessService $access,
    CurrentChildService $currentChildService
  ): void {
    $this->lesson = $access->lesson($lesson);

    $child = $currentChildService->current();

    abort_unless($child, 403);

    $this->progress = $child
      ->lessonProgress()
      ->where("lesson_id", $this->lesson->id)
      ->first();
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

  public function render()
  {
    return view("livewire.child.learning.lesson");
  }
}
