<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [

            // Math
            'math' => [
                [
                    'title' => 'Counting 1-10',
                    'age_group' => '3-5',
                    'description' => 'Learn numbers from 1 to 10.',
                ],
                [
                    'title' => 'Basic Addition',
                    'age_group' => '6-9',
                    'description' => 'Introduction to addition.',
                ],
            ],

            // AI for Kids
            'ai-for-kids' => [
                [
                    'title' => 'What Is AI?',
                    'age_group' => '6-9',
                    'description' => 'Discover what artificial intelligence is and where we see it every day.',
                ],
                [
                    'title' => 'Smart Helpers',
                    'age_group' => '3-5',
                    'description' => 'Meet smart assistants and learn how they help people.',
                ],
                [
                    'title' => 'AI Safety',
                    'age_group' => '6-9',
                    'description' => 'Learn how to use AI safely and responsibly.',
                ],
                [
                    'title' => 'AI Art Studio',
                    'age_group' => '6-9',
                    'description' => 'Create amazing artwork using AI tools.',
                ],
                [
                    'title' => 'Prompt Engineering for Kids',
                    'age_group' => '10-14',
                    'description' => 'Learn how to communicate effectively with AI systems.',
                ],
                [
                    'title' => 'Build Your First AI Project',
                    'age_group' => '10-14',
                    'description' => 'Create simple AI-powered projects and learn how AI works.',
                ],
            ],

            // Science
            'science' => [
                [
                    'title' => 'Amazing Animals',
                    'age_group' => '3-5',
                    'description' => 'Explore animals from around the world.',
                ],
                [
                    'title' => 'The Solar System',
                    'age_group' => '6-9',
                    'description' => 'Learn about planets and space.',
                ],
            ],

            // Reading
            'reading' => [
                [
                    'title' => 'Alphabet Adventure',
                    'age_group' => '3-5',
                    'description' => 'Learn letters and sounds.',
                ],
                [
                    'title' => 'Story Explorers',
                    'age_group' => '6-9',
                    'description' => 'Develop reading comprehension skills.',
                ],
            ],

        ];

        foreach ($courses as $subjectSlug => $subjectCourses) {

            $subject = Subject::where('slug', $subjectSlug)->first();

            if (! $subject) {
                continue;
            }

            foreach ($subjectCourses as $courseData) {

                Course::firstOrCreate(
                    [
                        'slug' => Str::slug($courseData['title']),
                    ],
                    [
                        'subject_id' => $subject->id,
                        'title' => $courseData['title'],
                        'description' => $courseData['description'],
                        'age_group' => $courseData['age_group'],
                        'is_published' => true,
                    ]
                );
            }
        }
    }
}