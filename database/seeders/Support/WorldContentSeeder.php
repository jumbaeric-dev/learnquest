<?php

namespace Database\Seeders\Support;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Skill;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Shared engine for every world content seeder.
 *
 * A world seeder only describes content. This class validates it,
 * writes it, links skills, and is safe to run repeatedly.
 */
abstract class WorldContentSeeder extends Seeder
{
    // XP economy. Level = floor(xp / 100) + 1, so a full course
    // is tuned to roughly 650 XP. Move to config/learnquest.php if preferred.
    protected const XP_STORY = 3;
    protected const XP_QUIZ = 5;
    protected const XP_PROJECT = 10;
    protected const XP_AI_CHAT = 8;
    protected const XP_CODING = 10;
    protected const XP_LESSON = 10;
    protected const XP_CHECKUP_LESSON = 20;

    // Matches the weight used by LearnQuestCurriculumSeeder.
    protected const SKILL_WEIGHT = 1;

    protected const AGE_GROUPS = ['3-5', '6-9', '10-14'];

    protected const ACTIVITY_TYPES = [
        'story',
        'quiz',
        'project',
        'ai_chat',
        'coding_challenge',
    ];

    protected const AI_SAFETY_RULES = 'Be kind and encouraging. Never ask for or accept real names, addresses, phone numbers, passwords, bank details, or photos. Stay on the lesson topic. If the child seems upset or unsafe, gently suggest talking to a trusted grown-up.';

    /**
     * New records are published on first seed so children can use them.
     * Existing records keep whatever publish status an admin gave them.
     */
    protected bool $publishOnCreate = true;

    abstract protected function worldSlug(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    abstract protected function courses(): array;

    public function run(): void
    {
        $courses = $this->courses();

        $this->validate($courses);

        $subject = Subject::query()
            ->where('slug', $this->worldSlug())
            ->first();

        if (! $subject) {
            throw new RuntimeException(
                "World '{$this->worldSlug()}' was not found. Run SubjectSeeder first."
            );
        }

        $skills = $this->loadSkills($courses);

        $totals = [
            'courses' => 0,
            'modules' => 0,
            'lessons' => 0,
            'activities' => 0,
            'xp' => 0,
        ];

        DB::transaction(function () use ($courses, $subject, $skills, &$totals): void {
            foreach ($courses as $courseIndex => $courseDef) {
                $course = $this->persist(
                    Course::class,
                    [
                        'slug' => Str::slug(
                            $this->worldSlug() . '-' . $courseDef['title'] . '-' . $courseDef['age_group']
                        ),
                    ],
                    [
                        'subject_id' => $subject->id,
                        'title' => $courseDef['title'],
                        'description' => $courseDef['description'],
                        'age_group' => $courseDef['age_group'],
                        'position' => $courseDef['position'] ?? ($courseIndex + 1),
                    ],
                    true
                );
                $totals['courses']++;

                foreach ($courseDef['modules'] as $moduleIndex => $moduleDef) {
                    $module = $this->persist(
                        CourseModule::class,
                        [
                            'course_id' => $course->id,
                            'title' => $moduleDef['title'],
                        ],
                        [
                            'description' => $moduleDef['description'],
                            'position' => $moduleIndex + 1,
                        ]
                    );
                    $totals['modules']++;

                    foreach ($moduleDef['lessons'] as $lessonIndex => $lessonDef) {
                        $lesson = $this->persist(
                            Lesson::class,
                            [
                                'learning_module_id' => $module->id,
                                'title' => $lessonDef['title'],
                            ],
                            [
                                'description' => $lessonDef['summary'],
                                'position' => $lessonIndex + 1,
                                'xp_reward' => $lessonDef['xp'],
                                'estimated_minutes' => $lessonDef['minutes'],
                            ],
                            true
                        );
                        $totals['lessons']++;
                        $totals['xp'] += $lessonDef['xp'];

                        foreach ($lessonDef['activities'] as $activityIndex => $activityDef) {
                            $activity = $this->persist(
                                LessonActivity::class,
                                [
                                    'lesson_id' => $lesson->id,
                                    'title' => $activityDef['title'],
                                ],
                                [
                                    'activity_type' => $activityDef['type'],
                                    'content' => $activityDef['content'],
                                    'position' => $activityIndex + 1,
                                    'xp_reward' => $activityDef['xp'],
                                ],
                                true
                            );
                            $totals['activities']++;
                            $totals['xp'] += $activityDef['xp'];

                            $pivot = [];

                            foreach (($activityDef['skills'] ?? $lessonDef['skills']) as $skillName) {
                                $pivot[$skills->get($skillName)->id] = [
                                    'weight' => self::SKILL_WEIGHT,
                                ];
                            }

                            $activity->skills()->sync($pivot);
                        }
                    }
                }
            }
        });

        $this->command?->info(sprintf(
            '%s: %d course(s), %d modules, %d lessons, %d activities, %d total XP available.',
            $this->worldSlug(),
            $totals['courses'],
            $totals['modules'],
            $totals['lessons'],
            $totals['activities'],
            $totals['xp']
        ));
    }

    /**
     * Create or update a record. On first create, optionally publish it.
     */
    private function persist(
        string $modelClass,
        array $identity,
        array $attributes,
        bool $publishable = false
    ): Model {
        $record = $modelClass::query()->where($identity)->first();

        if ($record) {
            $record->fill($attributes)->save();

            return $record;
        }

        $attributes = array_merge($identity, $attributes);

        if ($publishable) {
            $attributes['is_published'] = $this->publishOnCreate;
        }

        return $modelClass::query()->create($attributes);
    }

    private function loadSkills(array $courses): Collection
    {
        $names = [];

        foreach ($courses as $course) {
            foreach ($course['modules'] as $module) {
                foreach ($module['lessons'] as $lesson) {
                    foreach ($lesson['skills'] as $name) {
                        $names[$name] = true;
                    }

                    foreach ($lesson['activities'] as $activity) {
                        foreach (($activity['skills'] ?? []) as $name) {
                            $names[$name] = true;
                        }
                    }
                }
            }
        }

        $names = array_keys($names);

        $skills = Skill::query()
            ->where('is_active', true)
            ->whereIn('name', $names)
            ->get()
            ->keyBy('name');

        $missing = array_values(array_diff($names, $skills->keys()->all()));

        if ($missing !== []) {
            throw new RuntimeException(
                'Missing or inactive skills: ' . implode(', ', $missing)
                    . '. Run SkillSeeder and WorldSkillsSeeder first.'
            );
        }

        return $skills;
    }

    private function validate(array $courses): void
    {
        $this->check($courses !== [], 'courses() must return at least one course.');

        foreach ($courses as $c => $course) {
            $cp = "Course #{$c}";

            $this->check(
                filled($course['title'] ?? null) && filled($course['description'] ?? null),
                "{$cp}: title and description are required."
            );
            $this->check(
                in_array($course['age_group'] ?? null, self::AGE_GROUPS, true),
                "{$cp}: age_group must be 3-5, 6-9 or 10-14."
            );
            $this->check(! empty($course['modules']), "{$cp}: needs modules.");

            foreach ($course['modules'] as $m => $module) {
                $mp = "{$cp} / module #{$m}";

                $this->check(
                    filled($module['title'] ?? null) && ! empty($module['lessons']),
                    "{$mp}: needs a title and lessons."
                );

                $lessonTitles = [];

                foreach ($module['lessons'] as $l => $lesson) {
                    $lp = "{$mp} / lesson #{$l}";

                    $this->check(
                        filled($lesson['title'] ?? null) && filled($lesson['summary'] ?? null),
                        "{$lp}: title and summary are required."
                    );
                    $this->check(
                        ! in_array($lesson['title'], $lessonTitles, true),
                        "{$lp}: duplicate lesson title '{$lesson['title']}' in this module."
                    );
                    $lessonTitles[] = $lesson['title'];

                    $this->check(
                        ! empty($lesson['skills']) && ! empty($lesson['activities']),
                        "{$lp}: needs skills and activities."
                    );

                    $activityTitles = [];

                    foreach ($lesson['activities'] as $a => $activity) {
                        $ap = "{$lp} / activity #{$a}";

                        $this->check(
                            in_array($activity['type'] ?? null, self::ACTIVITY_TYPES, true),
                            "{$ap}: unknown activity type."
                        );
                        $this->check(filled($activity['title'] ?? null), "{$ap}: title is required.");
                        $this->check(
                            ! in_array($activity['title'], $activityTitles, true),
                            "{$ap}: duplicate activity title '{$activity['title']}' in this lesson."
                        );
                        $activityTitles[] = $activity['title'];

                        if ($activity['type'] === 'quiz') {
                            $content = $activity['content'];
                            $options = $content['options'] ?? [];

                            $this->check(
                                filled($content['question'] ?? null),
                                "{$ap}: quiz question is required."
                            );
                            $this->check(
                                count($options) >= 2 && count($options) <= 4,
                                "{$ap}: quiz needs 2 to 4 options."
                            );
                            $this->check(
                                is_int($content['answer'] ?? null)
                                    && array_key_exists($content['answer'], $options),
                                "{$ap}: quiz answer index is out of range."
                            );
                        }
                    }
                }
            }
        }
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new RuntimeException("Invalid world content: {$message}");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Content builders used by world seeders
    |--------------------------------------------------------------------------
    */

    protected static function course(
        string $title,
        string $description,
        string $ageGroup,
        array $modules,
        int $position = 1
    ): array {
        return [
            'title' => $title,
            'description' => $description,
            'age_group' => $ageGroup,
            'position' => $position,
            'modules' => $modules,
        ];
    }

    protected static function module(string $title, string $description, array $lessons): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'lessons' => $lessons,
        ];
    }

    protected static function lesson(
        string $title,
        string $summary,
        array $skills,
        array $activities,
        int $minutes = 8,
        ?int $xp = null
    ): array {
        $quizNumber = 0;

        foreach ($activities as $index => $activity) {
            if ($activity['type'] === 'quiz' && $activity['title'] === null) {
                $quizNumber++;
                $activities[$index]['title'] = "Quick Check {$quizNumber}";
            }
        }

        return [
            'title' => $title,
            'summary' => $summary,
            'skills' => $skills,
            'activities' => $activities,
            'minutes' => $minutes,
            'xp' => $xp ?? self::XP_LESSON,
        ];
    }

    /**
     * A lesson made only of quiz questions, used as a module test.
     */
    protected static function checkup(
        string $title,
        string $summary,
        array $skills,
        array $quizzes
    ): array {
        return self::lesson($title, $summary, $skills, $quizzes, 6, self::XP_CHECKUP_LESSON);
    }

    protected static function story(string $title, string $text): array
    {
        return [
            'type' => 'story',
            'title' => $title,
            'xp' => self::XP_STORY,
            'content' => [
                'instructions' => $text,
                'story_text' => $text,
            ],
        ];
    }

    protected static function quiz(
        string $question,
        array $options,
        int $answer,
        string $explanation = '',
        ?string $title = null
    ): array {
        $content = [
            'question' => $question,
            'options' => array_map(
                static fn (string $option): array => ['option' => $option],
                $options
            ),
            'answer' => $answer,
        ];

        if ($explanation !== '') {
            $content['explanation'] = $explanation;
        }

        return [
            'type' => 'quiz',
            'title' => $title,
            'xp' => self::XP_QUIZ,
            'content' => $content,
        ];
    }

    protected static function project(string $title, string $description, array $steps): array
    {
        $lines = [];

        foreach (array_values($steps) as $i => $step) {
            $lines[] = ($i + 1) . '. ' . $step;
        }

        return [
            'type' => 'project',
            'title' => $title,
            'xp' => self::XP_PROJECT,
            'content' => [
                'project_description' => $description . "\n\n" . implode("\n", $lines),
                'steps' => array_values($steps),
            ],
        ];
    }

    protected static function aiChat(
        string $title,
        string $goal,
        string $opener,
        string $persona
    ): array {
        return [
            'type' => 'ai_chat',
            'title' => $title,
            'xp' => self::XP_AI_CHAT,
            'content' => [
                'system_prompt' => $persona . ' ' . self::AI_SAFETY_RULES,
                'goal' => $goal,
                'instructions' => $opener,
            ],
        ];
    }

    protected static function challenge(
        string $title,
        string $challenge,
        string $difficulty = 'easy',
        array $hints = []
    ): array {
        return [
            'type' => 'coding_challenge',
            'title' => $title,
            'xp' => self::XP_CODING,
            'content' => [
                'challenge' => $challenge,
                'difficulty' => $difficulty,
                'hints' => $hints,
            ],
        ];
    }
}