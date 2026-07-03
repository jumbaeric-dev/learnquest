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

        $progress->xp += $xp;

        $progress->level = floor(
            $progress->xp / config('learnquest.xp_per_level')
        ) + 1;

        $progress->progress_percentage = min(
            round(
                (
                    $progress->xp /
                    config('learnquest.max_skill_xp')
                ) * 100,
                2
            ),
            100
        );

        $progress->save();

        return $progress;
    }

    /**
     * Award skill XP based on activity completion.
     */
    public function awardFromActivity(
        Child $child,
        LessonActivity $activity
    ): void {
        $activity->loadMissing('skills');

        foreach ($activity->skills as $skill) {
            $this->awardXp($child, $skill, $activity->xp_reward);
        }
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

    public function updateFromActivity(Child $child, $activity)
    {
        foreach ($activity->skills as $skill) {

            $progress = $child->skillProgress()->updateOrCreate(
                [
                    'skill_id' => $skill->id,
                ],
                [
                    'xp' => 0,
                    'level' => 1,
                ]
            );

            $progress->increment(
                'xp',
                $activity->xp_reward ?? 10
            );

            $maxXp = config('learnquest.max_skill_xp');

            if ($progress->xp > $maxXp) {
                $progress->xp = $maxXp;
            }

            $progress->level = (int) ceil($progress->xp / 200);

            $progress->save();
        }

        return true;
    }
}
