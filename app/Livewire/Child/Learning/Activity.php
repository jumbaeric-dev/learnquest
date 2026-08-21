<?php

namespace App\Livewire\Child\Learning;

use App\Models\ActivityProgress;
use App\Models\LessonActivity as LessonActivityModel;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\LearningContentAccessService;
use App\Services\Progress\LearningEngineService;
use Livewire\Component;

class Activity extends Component
{
  public LessonActivityModel $activity;

  public ?ActivityProgress $progress = null;

  public bool $submitted = false;

  public ?int $selectedOption = null;

  public ?int $score = null;

  public ?array $result = null;

  public function mount(
    LessonActivityModel $activity,
    LearningContentAccessService $access,
    CurrentChildService $currentChildService
  ): void {
    $this->activity = $access->activity($activity);

    $child = $currentChildService->current();

    abort_unless($child, 403);

    $this->progress = $child
      ->activityProgress()
      ->where("activity_id", $this->activity->id)
      ->first();

    if ($this->progress?->completed) {
      $this->submitted = true;
      $this->score = $this->progress->score;
    }
  }

  /**
   * Submit a quiz option.
   *
   * The correct answer is evaluated server-side.
   * The browser only sends the selected option index.
   */
  public function submitOption(
    int $option,
    CurrentChildService $currentChildService,
    LearningEngineService $learningEngine
  ): void {
    $child = $currentChildService->current();

    abort_unless($child, 403);

    if ($this->progress?->completed) {
      $this->submitted = true;
      $this->score = $this->progress->score;

      return;
    }

    $content = $this->activity->content ?? [];

    $options = $content["options"] ?? [];

    abort_unless(
      is_array($options) && array_key_exists($option, $options),
      422
    );

    $correctAnswer = $content["answer"] ?? null;

    $score = (int) $option === (int) $correctAnswer ? 100 : 0;

    $this->selectedOption = $option;
    $this->score = $score;

    $this->result = $learningEngine->completeActivity(
      $child,
      $this->activity,
      $score
    );

    $this->progress = $this->result["activity_progress"];

    $this->submitted = true;
  }

  /**
   * Complete a non-quiz activity.
   */
  public function complete(
    CurrentChildService $currentChildService,
    LearningEngineService $learningEngine
  ): void {
    $child = $currentChildService->current();

    abort_unless($child, 403);

    if ($this->progress?->completed) {
      $this->submitted = true;
      $this->score = $this->progress->score;

      return;
    }

    $this->score = 100;

    $this->result = $learningEngine->completeActivity(
      $child,
      $this->activity,
      100
    );

    $this->progress = $this->result["activity_progress"];

    $this->submitted = true;
  }

  public function render()
  {
    return view("livewire.child.learning.activity");
  }
}
