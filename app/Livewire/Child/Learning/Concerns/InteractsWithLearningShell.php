<?php

namespace App\Livewire\Child\Learning\Concerns;

use App\Models\Child;

/**
 * Supplies the data the child app shell needs (topbar summary + theme)
 * to full-page learning components.
 */
trait InteractsWithLearningShell
{
  /**
   * Topbar/profile summary for the app shell.
   *
   * @var array{name: string, level: int, xp: int, streak: int}
   */
  public array $explorer = [];

  /**
   * Visual theme applied to the app shell.
   */
  public string $theme = "sky";

  protected function prepareShell(Child $child, ?string $subjectSlug): void
  {
    $this->explorer = [
      "name" => $child->first_name,
      "level" => $child->level,
      "xp" => $child->xp,
      "streak" => $child->streak?->current_streak ?? 0,
    ];

    $this->theme = $this->themeForSubject($subjectSlug);
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
}