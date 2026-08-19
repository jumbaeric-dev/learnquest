<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Child;

class BadgeService
{
  public function __construct(
    protected LevelService $levelService,
    protected StreakService $streakService
  ) {
  }

  /**
   * Evaluate and award all eligible badges for a child.
   */
  public function evaluate(Child $child): array
  {
    $awarded = [];

    $badges = Badge::query()
      ->where("is_active", true)
      ->get();

    foreach ($badges as $badge) {
      if ($this->alreadyHasBadge($child, $badge)) {
        continue;
      }

      if (!$this->isEligible($child, $badge)) {
        continue;
      }

      if ($this->award($child, $badge)) {
        $awarded[] = $badge;
      }
    }

    return $awarded;
  }

  /**
   * Determine whether a child already has a badge.
   */
  protected function alreadyHasBadge(Child $child, Badge $badge): bool
  {
    return $child
      ->badges()
      ->where("badge_id", $badge->id)
      ->exists();
  }

  /**
   * Determine whether a child is eligible for a badge.
   */
  protected function isEligible(Child $child, Badge $badge): bool
  {
    $slug = $badge->slug;

    return match (true) {
      // XP badges: xp-100, xp-500, xp-1000, etc.
      str_starts_with($slug, "xp-") => $child->xp >=
        $this->thresholdFromSlug($slug, "xp-"),
      // Level badges: level-5, level-10, etc.
      str_starts_with($slug, "level-") => $child->level >=
        $this->thresholdFromSlug($slug, "level-"),
      // Streak badges: streak-3, streak-7, etc.
      str_starts_with($slug, "streak-") => $this->streakService->current(
        $child
      ) >= $this->thresholdFromSlug($slug, "streak-"),

      default => false,
    };
  }

  /**
   * Extract a numeric threshold from a badge slug.
   */
  protected function thresholdFromSlug(string $slug, string $prefix): int
  {
    return (int) str_replace($prefix, "", $slug);
  }

  /**
   * Award a badge to a child.
   *
   * Child::awardBadge() is the canonical badge-awarding path
   * because it also awards the badge's XP reward.
   */
  protected function award(Child $child, Badge $badge): bool
  {
    return $child->awardBadge($badge);
  }

  /**
   * Get all badges earned by a child.
   */
  public function earned(Child $child)
  {
    return $child->badges()->get();
  }

  /**
   * Determine whether a child has a badge by slug.
   */
  public function has(Child $child, string $slug): bool
  {
    return $child
      ->badges()
      ->where("slug", $slug)
      ->exists();
  }
}
