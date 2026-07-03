<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'subject_id' => Subject::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'age_group' => fake()->randomElement(['3-5', '6-9', '10-14']),
            'thumbnail' => null,
            'is_published' => true,
        ];
    }
}
