<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Child;

class AchievementService
{
  /**
   * Evaluate all achievement rules.
   */
  public function evaluate(Child $child): array
  {
    $awarded = [];

    $checks = [
      "firstLesson",
      "aiExplorer",
      "promptMaster",
      "aiSafetyChampion",
      "futureInnovator",
      "criticalThinker",
      "problemSolver",
      "learnQuestLegend",
      "streakBadges",
    ];

    foreach ($checks as $check) {
      $result = $this->{$check}($child);

      if (!empty($result)) {
        $awarded = array_merge($awarded, $result);
      }
    }

    return $awarded;
  }

  /**
   * Safely award a badge.
   *
   * Returns the badge when it was newly awarded,
   * otherwise null.
   */
  protected function awardBadge(Child $child, string $badgeSlug): ?Badge
  {
    $badge = Badge::query()
      ->where("slug", $badgeSlug)
      ->first();

    if (!$badge) {
      return null;
    }

    return $child->awardBadge($badge) ? $badge : null;
  }

  /**
   * First completed lesson.
   */
  protected function firstLesson(Child $child): array
  {
    $completed = $child
      ->lessonProgress()
      ->where("completed", true)
      ->exists();

    if (!$completed) {
      return [];
    }

    return array_filter([$this->awardBadge($child, "first-lesson")]);
  }

  /**
   * First completed course.
   */
  protected function aiExplorer(Child $child): array
  {
    $completed = $child
      ->courseProgress()
      ->where("completed", true)
      ->exists();

    if (!$completed) {
      return [];
    }

    return array_filter([$this->awardBadge($child, "ai-explorer")]);
  }

  /**
   * Complete a lesson whose title contains "prompt".
   */
  protected function promptMaster(Child $child): array
  {
    $completed = $child
      ->lessonProgress()
      ->whereHas(
        "lesson",
        fn($query) => $query->where("title", "like", "%prompt%")
      )
      ->where("completed", true)
      ->exists();

    if (!$completed) {
      return [];
    }

    return array_filter([$this->awardBadge($child, "prompt-master")]);
  }

  /**
   * Complete a lesson whose title contains "safety".
   */
  protected function aiSafetyChampion(Child $child): array
  {
    $completed = $child
      ->lessonProgress()
      ->whereHas(
        "lesson",
        fn($query) => $query->where("title", "like", "%safety%")
      )
      ->where("completed", true)
      ->exists();

    if (!$completed) {
      return [];
    }

    return array_filter([$this->awardBadge($child, "ai-safety-champion")]);
  }

  /**
   * Reach 500 XP.
   */
  protected function futureInnovator(Child $child): array
  {
    if ($child->xp < 500) {
      return [];
    }

    return array_filter([$this->awardBadge($child, "future-innovator")]);
  }

  /**
   * Complete 10 quiz activities.
   */
  protected function criticalThinker(Child $child): array
  {
    $count = $child
      ->activityProgress()
      ->where("completed", true)
      ->whereHas(
        "activity",
        fn($query) => $query->where("activity_type", "quiz")
      )
      ->count();

    if ($count < 10) {
      return [];
    }

    return array_filter([$this->awardBadge($child, "critical-thinker")]);
  }

  /**
   * Complete 3 project activities.
   */
  protected function problemSolver(Child $child): array
  {
    $count = $child
      ->activityProgress()
      ->where("completed", true)
      ->whereHas(
        "activity",
        fn($query) => $query->where("activity_type", "project")
      )
      ->count();

    if ($count < 3) {
      return [];
    }

    return array_filter([$this->awardBadge($child, "problem-solver")]);
  }

  /**
   * Earn at least 10 badges.
   */
  protected function learnQuestLegend(Child $child): array
  {
    if ($child->badges()->count() < 10) {
      return [];
    }

    return array_filter([$this->awardBadge($child, "learnquest-legend")]);
  }

  /**
   * Award streak achievements.
   */
  protected function streakBadges(Child $child): array
  {
    $streak = $child->streak;

    if (!$streak) {
      return [];
    }

    $awarded = [];

    if ($streak->current_streak >= 7) {
      $awarded[] = $this->awardBadge($child, "7-day-streak");
    }

    if ($streak->current_streak >= 30) {
      $awarded[] = $this->awardBadge($child, "30-day-streak");
    }

    if ($streak->current_streak >= 100) {
      $awarded[] = $this->awardBadge($child, "100-day-streak");
    }

    return array_filter($awarded);
  }
}
