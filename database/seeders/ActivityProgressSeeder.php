<?php

namespace Database\Seeders;

use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\LessonActivity;
use Illuminate\Database\Seeder;

class ActivityProgressSeeder extends Seeder
{
    /**
     * Seed the activity_progress table.
     */
    public function run(): void
    {
        $children = Child::all();
        $activities = LessonActivity::all();

        if ($children->isEmpty()) {
            $this->command->warn('No children found. Seed Children first.');

            return;
        }

        if ($activities->isEmpty()) {
            $this->command->warn('No activities found. Seed Activities first.');

            return;
        }

        foreach ($children as $child) {

            foreach ($activities as $activity) {

                // About 75% of activities are completed
                $completed = rand(1, 100) <= 75;

                // Realistic score
                $score = $completed
                    ? rand(70, 100)
                    : rand(20, 69);

                // XP earned only if completed
                $xpEarned = $completed
                    ? $activity->xp_reward
                    : 0;

                ActivityProgress::updateOrCreate(
                    [
                        'child_id' => $child->id,
                        'activity_id' => $activity->id,
                    ],
                    [
                        'completed' => $completed,

                        'score' => $score,

                        'xp_earned' => $xpEarned,

                        'completed_at' => $completed
                            ? now()->subDays(rand(0, 90))
                            : null,
                    ]
                );
            }
        }

        $this->command->info(
            'Activity Progress seeded successfully.'
        );
    }
}
