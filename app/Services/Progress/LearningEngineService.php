<?php

namespace App\Services\Progress;

use App\Models\Child;
use App\Models\LessonActivity;
use App\Services\AchievementService;
use App\Services\BadgeService;
use App\Services\FutureReadinessService;
use App\Services\LevelService;
use App\Services\StreakService;
use App\Services\XPService;
use Illuminate\Support\Facades\DB;

class LearningEngineService
{
  public function __construct(
    protected ActivityProgressService $activityProgressService,
    protected LessonProgressService $lessonProgressService,
    protected CourseProgressService $courseProgressService,
    protected SkillProgressService $skillProgressService,
    protected XPService $xpService,
    protected LevelService $levelService,
    protected StreakService $streakService,
    protected BadgeService $badgeService,
    protected AchievementService $achievementService,
    protected FutureReadinessService $futureReadinessService
  ) {
  }

  /**
   * Main learning orchestration entry point.
   */
  public function completeActivity(
    Child $child,
    LessonActivity $activity,
    int $score
  ): array {
    return DB::transaction(function () use ($child, $activity, $score) {
      /*
       * Lock the child row for the duration of the
       * progression transaction.
       *
       * This serializes competing learning events for
       * the same child.
       *
       * SQLite does not provide the same row-lock semantics
       * as MySQL/PostgreSQL, but the query remains portable
       * and the transaction gives us the correct production
       * behavior on row-locking databases.
       */
      $child = Child::query()
        ->whereKey($child->id)
        ->lockForUpdate()
        ->firstOrFail();

      // Determine whether this call creates a new completion event.
      $existingProgress = $this->activityProgressService->getProgress(
        $child,
        $activity
      );

      $wasAlreadyCompleted = $existingProgress?->completed === true;

      /*
       * 1. Activity progress
       */
      $activityProgress = $this->activityProgressService->complete(
        $child,
        $activity,
        $score
      );

      $justCompleted = $activityProgress->completed && !$wasAlreadyCompleted;
      /*
       * 2. Lesson progress
       */
      $lessonProgress = $this->lessonProgressService->update(
        $child,
        $activity->lesson
      );

      /*
       * 3. Course progress
       */
      $courseProgress = $this->courseProgressService->update(
        $child,
        $activity->lesson->module->course
      );

      /*
       * 4. Skill progress
       *
       * Skill progress is only awarded for successful
       * activity completion.
       */
      $skillProgress = [];

      if ($justCompleted) {
        $skillProgress = $this->skillProgressService->awardFromActivity(
          $child,
          $activity
        );
      }

      /*
       * 5. Streak
       */
      $streak = null;

      if ($justCompleted) {
        $streak = $this->streakService->recordActivity($child);
      }

      /*
       * 6. Child XP
       *
       * This is the canonical XP reward for completing an
       * activity. It is intentionally configuration-driven.
       *
       * The activity's xp_reward belongs to the activity/
       * skill progression system and should not determine
       * the global child completion XP.
       */
      if ($justCompleted) {
        $completionXp = $this->activityProgressService->xpRewardFor($activity);

        $this->xpService->award($child, $completionXp);
      }

      /*
       * 7. Level
       */
      $levelResult = $this->levelService->update($child);

      /*
       * 8. Badges
       *
       * Badge evaluation happens after XP and level updates
       * so XP/level-based badges can become eligible
       * immediately.
       */
      $badges = $this->badgeService->evaluate($child);

      /*
       * 9. Achievements
       */
      $achievements = $this->achievementService->evaluate($child);

      /*
       * 10. Future readiness
       */
      $futureReadiness = $this->futureReadinessService->build($child);

      return [
        "activity_progress" => $activityProgress,
        "lesson_progress" => $lessonProgress,
        "course_progress" => $courseProgress,
        "skill_progress" => $skillProgress,

        "streak" => $streak,

        "xp" => $child->fresh()->xp,
        "level" => $levelResult,

        "badges" => $badges,
        "achievements" => $achievements,

        "future_readiness" => $futureReadiness,
      ];
    });
  }
}
