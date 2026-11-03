<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Skill;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LegacyAiCoursePreservationSeeder extends Seeder
{
    private const MODULES = [
        'What Is AI?',
        'Smart Helpers',
        'AI Safety',
        'AI Art Studio',
        'Prompt Engineering for Kids',
        'Build Your First AI Project',
    ];

    private const LESSONS = [
        [
            'title' => 'Meet AI',
            'description' => 'An introductory look at artificial intelligence and its limitations.',
            'story_title' => 'Meet Robo the Friendly Helper',
            'story' => 'A learning app uses an AI tool to suggest a story idea. The suggestion can be useful, but the tool can also make mistakes and does not have human feelings.',
            'quiz_title' => 'What Is AI?',
            'question' => 'What should a learner remember about an AI tool?',
            'options' => [
                'It can make mistakes and its answers may need checking',
                'It always knows the truth',
                'It understands every situation exactly like a person',
            ],
            'answer' => 0,
            'story_skill' => 'AI Literacy',
            'quiz_skill' => 'AI Literacy',
        ],
        [
            'title' => 'AI Around Us',
            'description' => 'Explore examples of AI-enabled tools and think critically about their results.',
            'story_title' => 'Find AI Around You',
            'story' => 'Some digital tools use AI to suggest content, recognise speech, or organise information. A tool being new or impressive does not prove that its result is accurate.',
            'quiz_title' => 'Checking AI Results',
            'question' => 'What should you do before relying on an important AI answer?',
            'options' => [
                'Share it immediately',
                'Check important claims against trustworthy sources',
                'Assume it is correct because it came from a computer',
            ],
            'answer' => 1,
            'story_skill' => 'AI Literacy',
            'quiz_skill' => 'AI Limitations and Bias Awareness',
        ],
        [
            'title' => 'How AI Learns',
            'description' => 'Understand at a basic level how examples and data can influence AI outputs.',
            'story_title' => 'Teach the Robot',
            'story' => 'Some AI systems are developed using examples and data. If those examples are incomplete or unrepresentative, the system may perform poorly or produce unfair results in some situations.',
            'quiz_title' => 'Learning from Examples',
            'question' => 'Why might an AI system produce an incorrect or unfair result?',
            'options' => [
                'AI systems always understand everyone equally',
                'Its data, design, or context may be incomplete or biased',
                'AI systems cannot be affected by examples',
            ],
            'answer' => 1,
            'story_skill' => 'AI Literacy',
            'quiz_skill' => 'Data Awareness',
        ],
    ];

    public function run(): void
    {
        $requiredSkills = collect(self::LESSONS)
            ->flatMap(fn (array $lesson) => [
                $lesson['story_skill'],
                $lesson['quiz_skill'],
            ])
            ->unique()
            ->values()
            ->all();

        $skills = Skill::query()
            ->where('is_active', true)
            ->whereIn('name', $requiredSkills)
            ->get()
            ->keyBy('name');

        $missingSkills = array_values(array_diff(
            $requiredSkills,
            $skills->keys()->all()
        ));

        if ($missingSkills !== []) {
            throw new RuntimeException(
                'Legacy AI preservation requires these active skills: '
                . implode(', ', $missingSkills)
            );
        }

        DB::transaction(function () use ($skills): void {
            $subject = Subject::query()->firstOrCreate(
                ['slug' => 'ai-for-kids'],
                [
                    'name' => 'AI for Kids',
                    'description' => 'Archived legacy subject retained for historical continuity.',
                    'icon' => 'heroicon-o-cpu-chip',
                    'is_active' => false,
                ]
            );

            // Keep the legacy subject archived on every run.
            if ($subject->is_active) {
                $subject->is_active = false;
                $subject->save();
            }

            $course = Course::query()->firstOrCreate(
                ['slug' => 'ai-for-kids-course'],
                [
                    'subject_id' => $subject->id,
                    'title' => 'AI for Kids',
                    'description' => 'Legacy course retained for reference. It is unpublished and superseded by AI & Future Intelligence World.',
                    'age_group' => '10-14',
                    'is_published' => false,
                ]
            );

            $modules = [];

            foreach (self::MODULES as $index => $title) {
                $modules[$title] = CourseModule::query()->firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'title' => $title,
                    ],
                    [
                        'description' => 'Legacy module retained for reference; content requires review.',
                        'position' => $index + 1,
                    ]
                );
            }

            $module = $modules['What Is AI?'];

            foreach (self::LESSONS as $index => $definition) {
                $lesson = Lesson::query()->firstOrCreate(
                    [
                        'learning_module_id' => $module->id,
                        'title' => $definition['title'],
                    ],
                    [
                        'description' => $definition['description'],
                        'position' => $index + 1,
                        'xp_reward' => 10,
                        'estimated_minutes' => 5,
                        'is_published' => false,
                    ]
                );

                $story = LessonActivity::query()->firstOrCreate(
                    [
                        'lesson_id' => $lesson->id,
                        'title' => $definition['story_title'],
                    ],
                    [
                        'activity_type' => 'story',
                        'content' => [
                            'instructions' => $definition['story'],
                        ],
                        'position' => 1,
                        'xp_reward' => 5,
                        'is_published' => false,
                    ]
                );

                $quiz = LessonActivity::query()->firstOrCreate(
                    [
                        'lesson_id' => $lesson->id,
                        'title' => $definition['quiz_title'],
                    ],
                    [
                        'activity_type' => 'quiz',
                        'content' => [
                            'question' => $definition['question'],
                            'options' => array_map(
                                static fn (string $option): array => [
                                    'option' => $option,
                                ],
                                $definition['options']
                            ),
                            'answer' => $definition['answer'],
                        ],
                        'position' => 2,
                        'xp_reward' => 5,
                        'is_published' => false,
                    ]
                );

                $storySkill = $skills->get($definition['story_skill']);
                $quizSkill = $skills->get($definition['quiz_skill']);

                $story->skills()->syncWithoutDetaching([
                    $storySkill->id => ['weight' => 1],
                ]);

                $quiz->skills()->syncWithoutDetaching([
                    $quizSkill->id => ['weight' => 1],
                ]);
            }
        });

        $this->command?->info(
            'Legacy AI course preserved in an inactive, unpublished state.'
        );
    }
}