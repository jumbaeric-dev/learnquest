<?php

namespace App\Services\Progress;

use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\LessonActivity;
use InvalidArgumentException;

class ActivityProgressService
{
    /**
     * Get existing progress for a child/activity pair.
     */
    public function getProgress(
        Child $child,
        LessonActivity $activity
    ): ?ActivityProgress {
        return $child
            ->activityProgress()
            ->where('activity_id', $activity->id)
            ->first();
    }

    /**
     * Record or update a child's progress for an activity.
     *
     * This service owns activity-level state only.
     *
     * It does NOT:
     * - award XP
     * - update skill progress
     * - update lesson progress
     * - update course progress
     * - update streaks
     * - award badges
     * - award achievements
     *
     * Those responsibilities belong to the learning
     * orchestration layer and their respective services.
     */
    public function complete(
        Child $child,
        LessonActivity $activity,
        int $score
    ): ActivityProgress {
        $this->validateScore($score);

        $progress = $child
            ->activityProgress()
            ->where('activity_id', $activity->id)
            ->first();

        /*
         * Completion is permanent.
         *
         * Once the activity has transitioned to completed,
         * later attempts cannot modify the completion state.
         *
         * Most importantly, returning the existing row allows
         * the orchestration layer to distinguish an existing
         * completion from a newly-created completion event.
         */
        if ($progress?->completed) {
            return $progress->refresh();
        }

        $completed = $this->isPassing($score);

        return $child->activityProgress()->updateOrCreate(
            [
                'activity_id' => $activity->id,
            ],
            [
                'completed' => $completed,
                'score' => $score,
                'xp_earned' => $completed
                    ? $this->xpRewardFor($activity)
                    : 0,
                'completed_at' => $completed
                    ? now()
                    : null,
            ]
        );
    }

    /**
     * Determine whether a score passes the configured threshold.
     */
    public function isPassing(int $score): bool
    {
        return $score >= (int) config(
            'learnquest.activity_pass_score',
            70
        );
    }

    /**
     * Get the XP reward associated with an activity.
     *
     * Activity-specific reward takes precedence.
     * Application-level default is used as fallback.
     */
    public function xpRewardFor(
        LessonActivity $activity
    ): int {
        $reward = $activity->xp_reward;

        if ($reward === null) {
            return (int) config(
                'learnquest.activity_completion_xp',
                25
            );
        }

        return max(0, (int) $reward);
    }

    /**
     * Validate the submitted activity score.
     */
    protected function validateScore(int $score): void
    {
        if ($score < 0 || $score > 100) {
            throw new InvalidArgumentException(
                'Activity score must be between 0 and 100.'
            );
        }
    }
}