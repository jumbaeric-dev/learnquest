<?php

namespace App\Services;

use App\Models\Child;
use App\Models\LessonActivity;
use App\Services\Progress\ActivityProgressService;
use App\Services\Progress\CourseProgressService;
use App\Services\Progress\LessonProgressService;
use App\Services\Progress\SkillProgressService;
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
        protected FutureReadinessService $futureReadinessService,
    ) {}

    /**
     * MAIN ENTRY POINT
     */
    public function completeActivity(
        Child $child,
        LessonActivity $activity,
        int $score
    ): array {
        return DB::transaction(function () use ($child, $activity, $score) {

            // --------------------------------------------------
            // 1. ACTIVITY PROGRESS
            // --------------------------------------------------
            $activityProgress = $this->activityProgressService
                ->record($child, $activity, $score);

            // --------------------------------------------------
            // 2. LESSON PROGRESS
            // --------------------------------------------------
            $lessonProgress = $this->lessonProgressService
                ->updateFromActivity($child, $activity);

            // --------------------------------------------------
            // 3. COURSE PROGRESS
            // --------------------------------------------------
            $courseProgress = $this->courseProgressService
                ->updateFromLesson($child, $activity->lesson);

            // --------------------------------------------------
            // 4. SKILL PROGRESS
            // --------------------------------------------------
            $skillProgress = $this->skillProgressService
                ->updateFromActivity($child, $activity);

            // --------------------------------------------------
            // 5. STREAK
            // --------------------------------------------------
            $streak = $this->streakService
                ->recordActivity($child);

            // --------------------------------------------------
            // 6. XP
            // --------------------------------------------------
            $this->xpService->awardForActivity($child);

            // --------------------------------------------------
            // 7. LEVEL
            // --------------------------------------------------
            $levelResult = $this->levelService->update($child);

            // --------------------------------------------------
            // 8. BADGES
            // --------------------------------------------------
            $badges = $this->badgeService->evaluate($child);

            // --------------------------------------------------
            // 9. ACHIEVEMENTS
            // --------------------------------------------------
            $achievements = $this->achievementService->evaluate($child);

            // --------------------------------------------------
            // 10. FUTURE READINESS
            // --------------------------------------------------
            $futureReadiness = $this->futureReadinessService
                ->build($child);

            // --------------------------------------------------
            // RETURN SUMMARY
            // --------------------------------------------------
            return [
                'activity_progress' => $activityProgress,
                'lesson_progress' => $lessonProgress,
                'course_progress' => $courseProgress,
                'skill_progress' => $skillProgress,

                'streak' => $streak,

                'xp' => $child->xp,
                'level' => $levelResult,

                'badges' => $badges,
                'achievements' => $achievements,

                'future_readiness' => $futureReadiness,
            ];
        });
    }
}
