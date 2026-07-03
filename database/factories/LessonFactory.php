<?php

namespace Database\Factories;

use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        return [
            'learning_module_id' => CourseModule::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'position' => 1,
            'xp_reward' => 10,
            'estimated_minutes' => 5,
            'is_published' => true,
        ];
    }
}
