<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonActivity>
 */
class LessonActivityFactory extends Factory
{
    protected $model = LessonActivity::class;

    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'title' => fake()->sentence(3),
            'activity_type' => 'quiz',
            'content' => [
                'question' => fake()->sentence().'?',
                'options' => [
                    ['option' => 'A'],
                    ['option' => 'B'],
                ],
                'answer' => 0,
            ],
            'position' => 1,
            'xp_reward' => 5,
            'is_published' => true,
        ];
    }
}
