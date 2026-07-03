<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\LessonProgress;
use Illuminate\Database\Seeder;

class CourseProgressSeeder extends Seeder
{
    /**
     * Seed the course_progress table.
     */
    public function run(): void
    {
        $children = Child::all();

        $courses = Course::with('modules.lessons')->get();

        if ($children->isEmpty()) {
            $this->command->warn('No children found.');

            return;
        }

        if ($courses->isEmpty()) {
            $this->command->warn('No courses found.');

            return;
        }

        foreach ($children as $child) {

            foreach ($courses as $course) {

                // Collect every lesson in every module
                $lessonIds = $course->modules
                    ->flatMap(function ($module) {
                        return $module->lessons;
                    })
                    ->pluck('id');

                $totalLessons = $lessonIds->count();

                if ($totalLessons === 0) {
                    continue;
                }

                $completedLessons = LessonProgress::query()
                    ->where('child_id', $child->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('completed', true)
                    ->count();

                $progressPercentage = round(
                    ($completedLessons / $totalLessons) * 100,
                    2
                );

                $completed = $completedLessons === $totalLessons;

                CourseProgress::updateOrCreate(
                    [
                        'child_id' => $child->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'completed_lessons' => $completedLessons,

                        'total_lessons' => $totalLessons,

                        'progress_percentage' => $progressPercentage,

                        'completed' => $completed,

                        'completed_at' => $completed
                            ? now()->subDays(rand(0, 30))
                            : null,
                    ]
                );
            }
        }

        $this->command->info(
            'Course Progress seeded successfully.'
        );
    }
}
