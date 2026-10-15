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

  /**
   * Topbar/profile summary for the app shell.
   *
   * @var array{name: string, level: int, xp: int, streak: int}
   */
  public array $explorer = [];

  /**
   * Visual theme applied to the app shell for this course.
   */
  public string $theme = "sky";

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

    $this->explorer = [
      "name" => $child->first_name,
      "level" => $child->level,
      "xp" => $child->xp,
      "streak" => $child->streak?->current_streak ?? 0,
    ];

    $this->theme = $this->themeForSubject($this->course->subject?->slug);
  }

  /**
   * Map a subject to one of the LQDS app-shell themes.
   */
  protected function themeForSubject(?string $slug): string
  {
    return match ($slug) {
      "science-lab" => "science",
      "code-city", "ai-academy" => "coding",
      "life-skills-village" => "finance",
      "reading-kingdom", "creative-studio" => "explorer",
      default => "sky",
    };
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

  public function getTotalXpProperty(): int
  {
    return (int) $this->course->modules
      ->flatMap(fn($module) => $module->lessons)
      ->sum("xp_reward");
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
