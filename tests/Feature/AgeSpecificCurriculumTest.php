<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonActivity;
use Database\Seeders\LearnQuestCurriculumSeeder;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgeSpecificCurriculumTest extends TestCase
{
    use RefreshDatabase;

    private const AGE_GROUPS = ['3-5', '6-9', '10-14'];

    private const WORLDS = [
        'self-discovery',
        'growth-habits',
        'people-relationships',
        'critical-thinking',
        'digital-life-safety',
        'money-financial-wisdom',
        'entrepreneurship-innovation',
        'ai-future-intelligence',
        'coding-robotics-engineering',
        'science-nature-universe',
        'healthy-practical-life',
        'leadership-citizenship-global-futures',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SubjectSeeder::class);
        $this->seed(SkillSeeder::class);
        $this->seed(LearnQuestCurriculumSeeder::class);
    }

    public function test_every_world_has_distinct_valid_content_for_all_age_groups(): void
    {
        $courseCount = 0;
        $lessonCount = 0;
        $activityCount = 0;

        $storiesByLesson = [];
        $questionsByLesson = [];
        $descriptionsByWorld = [];

        foreach (self::WORLDS as $worldSlug) {
            foreach (self::AGE_GROUPS as $ageGroup) {
                $course = Course::query()
                    ->where('age_group', $ageGroup)
                    ->whereHas(
                        'subject',
                        fn ($query) => $query->where('slug', $worldSlug)
                    )
                    ->firstOrFail();

                $courseCount++;

                $this->assertFalse(
                    (bool) $course->is_published,
                    "{$worldSlug}/{$ageGroup} must remain unpublished."
                );

                $descriptionsByWorld[$worldSlug][] = $course->description;

                $lessons = Lesson::query()
                    ->whereHas(
                        'module',
                        fn ($query) => $query->where('course_id', $course->id)
                    )
                    ->orderBy('position')
                    ->get();

                $this->assertCount(
                    4,
                    $lessons,
                    "{$worldSlug}/{$ageGroup} should contain four lessons."
                );

                foreach ($lessons as $lesson) {
                    $lessonCount++;

                    $this->assertFalse(
                        (bool) $lesson->is_published,
                        "{$lesson->title} must remain unpublished."
                    );

                    $this->assertStringNotContainsString(
                        'Learning guidance:',
                        (string) $lesson->description
                    );

                    $activities = LessonActivity::query()
                        ->where('lesson_id', $lesson->id)
                        ->get();

                    $this->assertCount(
                        2,
                        $activities,
                        "{$lesson->title} should have two activities."
                    );

                    $story = $activities->firstWhere('activity_type', 'story');
                    $quiz = $activities->firstWhere('activity_type', 'quiz');

                    $this->assertNotNull($story);
                    $this->assertNotNull($quiz);

                    $activityCount += $activities->count();

                    $this->assertFalse((bool) $story->is_published);
                    $this->assertFalse((bool) $quiz->is_published);

                    $this->assertTrue(
                        $story->skills()->where('is_active', true)->exists(),
                        "{$story->title} must link to an active skill."
                    );

                    $this->assertTrue(
                        $quiz->skills()->where('is_active', true)->exists(),
                        "{$quiz->title} must link to an active skill."
                    );

                    $storyContent = $story->content;
                    $this->assertIsArray($storyContent);

                    $storyText = $storyContent['instructions'] ?? null;
                    $this->assertIsString($storyText);
                    $this->assertNotSame('', trim($storyText));

                    $this->assertStringNotContainsString(
                        'Learning guidance:',
                        $storyText
                    );

                    $quizContent = $quiz->content;
                    $this->assertIsArray($quizContent);

                    $question = $quizContent['question'] ?? null;
                    $this->assertIsString($question);
                    $this->assertNotSame('', trim($question));

                    $options = $quizContent['options'] ?? null;
                    $this->assertIsArray($options);
                    $this->assertGreaterThanOrEqual(2, count($options));

                    foreach ($options as $option) {
                        $this->assertIsArray($option);
                        $this->assertIsString($option['option'] ?? null);
                        $this->assertNotSame('', trim($option['option']));
                    }

                    $answer = $quizContent['answer'] ?? null;
                    $this->assertIsInt($answer);
                    $this->assertArrayHasKey($answer, $options);

                    $key = $worldSlug . '|' . $lesson->title;

                    $storiesByLesson[$key][] = $storyText;
                    $questionsByLesson[$key][] = $question;
                }
            }

            $this->assertCount(
                3,
                array_unique($descriptionsByWorld[$worldSlug]),
                "{$worldSlug} must have an age-specific description for each age group."
            );
        }

        $this->assertSame(36, $courseCount);
        $this->assertSame(144, $lessonCount);
        $this->assertSame(288, $activityCount);

        foreach (self::WORLDS as $worldSlug) {
            foreach ($storiesByLesson as $key => $stories) {
                if (! str_starts_with($key, $worldSlug . '|')) {
                    continue;
                }

                $this->assertCount(
                    3,
                    array_unique($stories),
                    "{$key} must have three distinct stories."
                );

                $this->assertCount(
                    3,
                    array_unique($questionsByLesson[$key]),
                    "{$key} must have three distinct quiz questions."
                );
            }
        }
    }

    public function test_rerunning_the_seeder_preserves_reviewed_publication_status(): void
    {
        $course = Course::query()
            ->where('age_group', '6-9')
            ->whereHas(
                'subject',
                fn ($query) => $query->where('slug', 'self-discovery')
            )
            ->firstOrFail();

        $course->is_published = true;
        $course->save();

        $lesson = Lesson::query()
            ->where('title', 'What Makes Me Unique?')
            ->whereHas('module.course', function ($query) {
                $query
                    ->where('age_group', '6-9')
                    ->whereHas(
                        'subject',
                        fn ($subjectQuery) => $subjectQuery
                            ->where('slug', 'self-discovery')
                    );
            })
            ->firstOrFail();

        $lesson->is_published = true;
        $lesson->save();

        $story = LessonActivity::query()
            ->where('lesson_id', $lesson->id)
            ->where('activity_type', 'story')
            ->firstOrFail();

        $story->is_published = true;
        $story->save();

        $this->seed(LearnQuestCurriculumSeeder::class);

        $this->assertTrue((bool) $course->fresh()->is_published);
        $this->assertTrue((bool) $lesson->fresh()->is_published);
        $this->assertTrue((bool) $story->fresh()->is_published);

        $this->assertSame(
            36,
            Course::query()
                ->whereHas(
                    'subject',
                    fn ($query) => $query->whereIn('slug', self::WORLDS)
                )
                ->count()
        );

        $this->assertSame(
            144,
            Lesson::query()
                ->whereHas(
                    'module.course.subject',
                    fn ($query) => $query->whereIn('slug', self::WORLDS)
                )
                ->count()
        );

        $this->assertSame(
            288,
            LessonActivity::query()
                ->whereHas(
                    'lesson.module.course.subject',
                    fn ($query) => $query->whereIn('slug', self::WORLDS)
                )
                ->count()
        );
    }
}