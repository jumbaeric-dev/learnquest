<?php

namespace App\Services;

use App\Models\Child;
use App\Models\Skill;
use Illuminate\Support\Collection;

class FutureReadinessService
{
  /**
   * Build the complete future-readiness profile for a child.
   */
  public static function build(Child $child): array
  {
    $skills = $child
      ->skillProgress()
      ->with("skill")
      ->get();

    return [
      "overall_score" => self::calculateScore($child),

      "level" => self::calculateLevel($child),

      "skills" => $skills
        ->map(function ($progress) {
          return [
            "skill" => $progress->skill?->name,
            "xp" => $progress->xp,
            "level" => $progress->level,
            "progress" => (float) $progress->progress_percentage,
          ];
        })
        ->values(),

      "strongest_skills" => self::strongestSkills(
        $child,
        (int) config("learnquest.future_readiness.strongest_skills_limit", 5)
      ),

      "weakest_skills" => self::weakestSkills(
        $child,
        (int) config("learnquest.future_readiness.weakest_skills_limit", 5)
      ),

      "radar_data" => self::radarData($child),
    ];
  }

  /**
   * Calculate overall future-readiness score for a child.
   */
  public static function calculateScore(Child $child): float
  {
    $skills = $child->skillProgress;

    if ($skills->isEmpty()) {
      return 0.0;
    }

    return round($skills->avg("progress_percentage"), 2);
  }

  /**
   * Convert the readiness score into a learner-facing level.
   */
  public static function calculateLevel(Child $child): string
  {
    $score = self::calculateScore($child);

    $levels = config("learnquest.future_readiness.levels", []);

    foreach ($levels as $level) {
      if ($score >= $level["min_score"]) {
        return $level["title"];
      }
    }

    return "Explorer";
  }

  /**
   * Strongest skills for a specific child.
   *
   * Ordered by the child's skill XP.
   */
  public static function strongestSkills(
    Child $child,
    int $limit = 5
  ): Collection {
    return $child
      ->skillProgress()
      ->with("skill")
      ->orderByDesc("xp")
      ->take($limit)
      ->get();
  }

  /**
   * Weakest skills for a specific child.
   *
   * Ordered by the child's skill XP.
   */
  public static function weakestSkills(Child $child, int $limit = 5): Collection
  {
    return $child
      ->skillProgress()
      ->with("skill")
      ->orderBy("xp")
      ->take($limit)
      ->get();
  }

  /**
   * Get the strongest skill name for a child.
   */
  public static function strongestSkill(Child $child): ?string
  {
    return $child
      ->skillProgress()
      ->with("skill")
      ->orderByDesc("xp")
      ->first()?->skill?->name;
  }

  /**
   * Get the weakest skill name for a child.
   */
  public static function weakestSkill(Child $child): ?string
  {
    return $child
      ->skillProgress()
      ->with("skill")
      ->orderBy("xp")
      ->first()?->skill?->name;
  }

  /**
   * Radar-chart dataset for a child.
   */
  public static function radarData(Child $child): array
  {
    $skills = $child
      ->skillProgress()
      ->with("skill")
      ->get();

    return [
      "labels" => $skills
        ->map(fn($progress) => $progress->skill?->name)
        ->filter()
        ->values()
        ->toArray(),

      "values" => $skills
        ->map(fn($progress) => (float) $progress->progress_percentage)
        ->values()
        ->toArray(),
    ];
  }

  /**
   * Full learner profile.
   */
  public static function profile(Child $child): array
  {
    return [
      "score" => self::calculateScore($child),

      "level" => self::calculateLevel($child),

      "strongest_skills" => $child->strongestSkills(),

      "weakest_skills" => $child->weakestSkills(),

      "radar_data" => self::radarData($child),
    ];
  }

  /**
   * Platform-wide average readiness.
   */
  public static function platformAverage(): float
  {
    $children = Child::with("skillProgress")->get();

    if ($children->isEmpty()) {
      return 0.0;
    }

    return round($children->avg(fn($child) => self::calculateScore($child)), 2);
  }

  /**
   * Top future-ready learners.
   */
  public static function topLearners(int $limit = 10): Collection
  {
    return Child::with("skillProgress")
      ->get()
      ->sortByDesc(fn($child) => self::calculateScore($child))
      ->take($limit)
      ->values();
  }
}
