<?php

namespace Database\Seeders;

use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Database\Seeder;

class LessonProgressSeeder extends Seeder
{
    /**
     * Seed the lesson_progress table.
     */
    public function run(): void
    {
        $children = Child::all();
        $lessons = Lesson::with('activities')->get();

        if ($children->isEmpty()) {
            $this->command->warn('No children found.');

            return;
        }

        if ($lessons->isEmpty()) {
            $this->command->warn('No lessons found.');

            return;
        }

        foreach ($children as $child) {

            foreach ($lessons as $lesson) {

                $activityIds = $lesson->activities->pluck('id');

                $totalActivities = $activityIds->count();

                if ($totalActivities === 0) {
                    continue;
                }

                $completedActivities = ActivityProgress::query()
                    ->where('child_id', $child->id)
                    ->whereIn('activity_id', $activityIds)
                    ->where('completed', true)
                    ->count();

                $progressPercentage = round(
                    ($completedActivities / $totalActivities) * 100,
                    2
                );

                $completed = $completedActivities === $totalActivities;

                LessonProgress::updateOrCreate(
                    [
                        'child_id' => $child->id,
                        'lesson_id' => $lesson->id,
                    ],
                    [
                        'completed_activities' => $completedActivities,

                        'total_activities' => $totalActivities,

                        'progress_percentage' => $progressPercentage,

                        'completed' => $completed,

                        'completed_at' => $completed
                            ? now()->subDays(rand(0, 60))
                            : null,
                    ]
                );
            }
        }

        $this->command->info(
            'Lesson Progress seeded successfully.'
        );
    }
}
