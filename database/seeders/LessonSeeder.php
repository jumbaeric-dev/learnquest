<?php

namespace Database\Seeders;

use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $module = CourseModule::first();

        if (! $module) {
            return;
        }

        Lesson::firstOrCreate([
            'learning_module_id' => $module->id,
            'title' => 'What Is AI?',
        ], [
            'description' => 'Introduction to artificial intelligence.',
            'position' => 1,
            'xp_reward' => 10,
            'estimated_minutes' => 5,
            'is_published' => true,
        ]);

        Lesson::firstOrCreate([
            'learning_module_id' => $module->id,
            'title' => 'AI Around Us',
        ], [
            'description' => 'Discover AI in daily life.',
            'position' => 2,
            'xp_reward' => 10,
            'estimated_minutes' => 5,
            'is_published' => true,
        ]);
    }
}
