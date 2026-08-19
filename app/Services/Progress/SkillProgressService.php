<?php

namespace App\Services\Progress;

use App\Models\Child;
use App\Models\ChildSkillProgress;
use App\Models\LessonActivity;
use App\Models\Skill;

class SkillProgressService
{
    /**
     * Award XP to a child's skill.
     */
    public function awardXp(
        Child $child,
        Skill $skill,
        int $xp
    ): ChildSkillProgress {
        $progress = ChildSkillProgress::firstOrCreate(
            [
                'child_id' => $child->id,
                'skill_id' => $skill->id,
            ],
            [
                'xp' => 0,
                'level' => 1,
                'progress_percentage' => 0,
            ]
        );

        $maxXp = (int) config(
            'learnquest.max_skill_xp',
            1000
        );

        $progress->xp = min(
            $maxXp,
            $progress->xp + max(0, $xp)
        );

        $xpPerLevel = (int) config(
            'learnquest.xp_per_level',
            100
        );

        $progress->level = max(
            1,
            (int) floor($progress->xp / $xpPerLevel) + 1
        );

        $progress->progress_percentage = min(
            100,
            round(
                ($progress->xp / $maxXp) * 100,
                2
            )
        );

        $progress->save();

        return $progress->refresh();
    }

    /**
     * Award skill XP based on activity completion.
     *
     * Returns the affected skill progress records so the
     * learning engine can expose them in its result.
     *
     * @return array<int, ChildSkillProgress>
     */
    public function awardFromActivity(
        Child $child,
        LessonActivity $activity
    ): array {
        $activity->loadMissing('skills');

        $progress = [];

        foreach ($activity->skills as $skill) {
            $progress[] = $this->awardXp(
                $child,
                $skill,
                $activity->xp_reward
            );
        }

        return $progress;
    }

    /**
     * Get a child's progress for a skill.
     */
    public function getProgress(
        Child $child,
        Skill $skill
    ): ?ChildSkillProgress {
        return ChildSkillProgress::query()
            ->where('child_id', $child->id)
            ->where('skill_id', $skill->id)
            ->first();
    }

    /**
     * Determine if a skill has reached 100%.
     */
    public function isMastered(
        Child $child,
        Skill $skill
    ): bool {
        return optional(
            $this->getProgress($child, $skill)
        )->progress_percentage >= 100;
    }

    /**
     * Update skill progress from an activity.
     *
     * Kept as the orchestration-facing method, but delegates
     * to the canonical awardXp() implementation.
     */
    public function updateFromActivity(
        Child $child,
        LessonActivity $activity
    ): bool {
        $activity->loadMissing('skills');

        foreach ($activity->skills as $skill) {
            $this->awardXp(
                $child,
                $skill,
                $activity->xp_reward ?? config(
                    'learnquest.activity_completion_xp',
                    25
                )
            );
        }

        return true;
    }
}