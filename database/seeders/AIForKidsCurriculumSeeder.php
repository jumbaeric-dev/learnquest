<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Skill;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AIForKidsCurriculumSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Subject
        |--------------------------------------------------------------------------
        */

        $subject = Subject::firstOrCreate(
            ['slug' => 'ai-for-kids'],
            [
                'name' => 'AI for Kids',
                'description' => 'A fun introduction to artificial intelligence for young learners.',
                'icon' => 'heroicon-o-cpu-chip',
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Course
        |--------------------------------------------------------------------------
        */

        $course = Course::firstOrCreate(
            ['slug' => 'ai-for-kids-course'],
            [
                'subject_id' => $subject->id,
                'title' => 'AI for Kids',
                'description' => 'Prepare children for an AI-powered future.',
                'age_group' => '10-14',
                'is_published' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Curriculum Structure
        |--------------------------------------------------------------------------
        */

        $curriculum = [

            'What Is AI?' => [

                'Meet AI' => [
                    [
                        'title' => 'Meet Robo the Friendly Helper',
                        'type' => 'story',
                        'xp' => 10,
                        'skills' => [
                            'AI Literacy',
                            'Communication',
                        ],
                        'content' => [
                            'story_text' =>
                                'Robo is a friendly AI helper who loves helping children learn.',
                        ],
                    ],

                    [
                        'title' => 'What Is AI?',
                        'type' => 'quiz',
                        'xp' => 15,
                        'skills' => [
                            'AI Literacy',
                            'Critical Thinking',
                        ],
                        'content' => [
                            'question' => 'What does AI stand for?',
                            'options' => [
                                ['option' => 'Artificial Intelligence'],
                                ['option' => 'Animal Intelligence'],
                                ['option' => 'Automatic Internet'],
                            ],
                            'answer' => 0,
                        ],
                    ],
                ],

                'AI Around Us' => [
                    [
                        'title' => 'Find AI Around You',
                        'type' => 'project',
                        'xp' => 20,
                        'skills' => [
                            'AI Literacy',
                            'Problem Solving',
                        ],
                        'content' => [
                            'project_description' =>
                                'Find five examples of AI in your daily life.',
                        ],
                    ],
                ],

                'How AI Learns' => [
                    [
                        'title' => 'Teach the Robot',
                        'type' => 'ai_chat',
                        'xp' => 20,
                        'skills' => [
                            'AI Literacy',
                            'Communication',
                        ],
                        'content' => [
                            'system_prompt' =>
                                'You are a friendly robot learning from children.',
                            'goal' =>
                                'Help learners understand how AI learns from examples.',
                        ],
                    ],

                    [
                        'title' => 'Train Your First AI',
                        'type' => 'coding_challenge',
                        'xp' => 25,
                        'skills' => [
                            'AI Literacy',
                            'Coding',
                            'Problem Solving',
                        ],
                        'content' => [
                            'challenge' =>
                                'Teach a robot to recognize cats and dogs.',
                            'difficulty' => 'easy',
                        ],
                    ],
                ],
            ],

            'Smart Helpers' => [],

            'AI Safety' => [],

            'AI Art Studio' => [],

            'Prompt Engineering for Kids' => [],

            'Build Your First AI Project' => [],
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Curriculum
        |--------------------------------------------------------------------------
        */

        $modulePosition = 1;

        foreach ($curriculum as $moduleTitle => $lessons) {

            $module = CourseModule::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => $moduleTitle,
                ],
                [
                    'description' => $moduleTitle,
                    'position' => $modulePosition,
                ]
            );

            $lessonPosition = 1;

            foreach ($lessons as $lessonTitle => $activities) {

                $lesson = Lesson::firstOrCreate(
                    [
                        'learning_module_id' => $module->id,
                        'title' => $lessonTitle,
                    ],
                    [
                        'description' => $lessonTitle,
                        'position' => $lessonPosition,
                        'is_published' => true,
                    ]
                );

                $activityPosition = 1;

                foreach ($activities as $activityData) {

                    $activity = LessonActivity::firstOrCreate(
                        [
                            'lesson_id' => $lesson->id,
                            'title' => $activityData['title'],
                        ],
                        [
                            'activity_type' => $activityData['type'],
                            'content' => $activityData['content'],
                            'position' => $activityPosition,
                            'xp_reward' => $activityData['xp'],
                            'is_published' => true,
                        ]
                    );

                    $skillIds = Skill::whereIn(
                        'name',
                        $activityData['skills']
                    )->pluck('id');

                    $activity->skills()->syncWithoutDetaching($skillIds);

                    $activityPosition++;
                }

                $lessonPosition++;
            }

            $modulePosition++;
        }
    }
}