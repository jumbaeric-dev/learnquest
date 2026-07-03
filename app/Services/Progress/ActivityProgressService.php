<?php

namespace App\Services\Progress;

use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\LessonActivity;
use App\Services\AchievementService;
use App\Services\StreakService;

class ActivityProgressService
{
    public function __construct(
        protected StreakService $streakService,
        protected AchievementService $achievementService,
        protected SkillProgressService $skillProgressService,
        protected LessonProgressService $lessonProgressService,
        protected CourseProgressService $courseProgressService,
    ) {}

    /**
     * Record or update a child's progress for an activity.
     */
    public function complete(
        Child $child,
        LessonActivity $activity,
        int $score
    ): ActivityProgress {
        $completed = $score >= config('learnquest.activity_pass_score');

        $progress = ActivityProgress::updateOrCreate(
            [
                'child_id' => $child->id,
                'activity_id' => $activity->id,
            ],
            [
                'completed' => $completed,
                'score' => $score,
                'xp_earned' => $completed ? $activity->xp_reward : 0,
                'completed_at' => $completed ? now() : null,
            ]
        );

        if ($completed && ($progress->wasRecentlyCreated || $progress->wasChanged('completed'))) {
            $child->addXp($activity->xp_reward);
            $this->skillProgressService->awardFromActivity($child, $activity);
            $this->streakService->recordActivity($child);
        }

        $lesson = $activity->lesson;

        if ($lesson) {
            $this->lessonProgressService->update($child, $lesson);

            $course = $lesson->module?->course;

            if ($course) {
                $this->courseProgressService->update($child, $course);
            }
        }

        $this->achievementService->evaluate($child);

        return $progress;
    }

    public function record(Child $child, $activity, int $score)
    {
        return $child->activityProgress()->updateOrCreate(
            [
                'activity_id' => $activity->id,
            ],
            [
                'completed' => true,
                'score' => $score,
                'xp_earned' => $this->calculateXp($score),
                'completed_at' => now(),
            ]
        );
    }

    protected function calculateXp(int $score): int
    {
        $passScore = config('learnquest.activity_pass_score');

        return $score >= $passScore
            ? config('learnquest.activity_completion_xp', 25)
            : 5;
    }

}
