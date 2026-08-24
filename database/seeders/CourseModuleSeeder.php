<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Database\Seeder;

class CourseModuleSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'counting-1-10')->first();

        if (! $course) {
            return;
        }

        CourseModule::firstOrCreate([
            'course_id' => $course->id,
            'title' => 'Numbers 1-5',
        ], [
            'description' => 'Learn numbers 1 through 5',
            'position' => 1,
        ]);

        CourseModule::firstOrCreate([
            'course_id' => $course->id,
            'title' => 'Numbers 6-10',
        ], [
            'description' => 'Learn numbers 6 through 10',
            'position' => 2,
        ]);
    }
}
