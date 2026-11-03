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

class SelfDiscoveryAgeSpecificCurriculumTest extends TestCase
{
    use RefreshDatabase;

    private const AGE_GROUPS = [
        '3-5',
        '6-9',
        '10-14',
    ];

    private const LESSON_TITLES = [
        'What Makes Me Unique?',
        'Discovering My Strengths',
        'Naming My Feelings',
        'Growing My Confidence',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SubjectSeeder::class);
        $this->seed(SkillSeeder::class);
        $this->seed(LearnQuestCurriculumSeeder::class);
    }

    public function test_each_age_band_has_distinct_valid_self_discovery_content(): void
    {
        $storiesByLesson = [];
        $questionsByLesson = [];
        $courseDescriptions = [];

        foreach (self::AGE_GROUPS as $ageGroup) {
            $course = Course::query()
                ->where('age_group', $ageGroup)
                ->whereHas(
                    'subject',
                    fn($query) => $query->where('slug', 'self-discovery')
                )
                ->firstOrFail();

            $this->assertFalse(
                (bool) $course->is_published,
                "The {$ageGroup} Self-Discovery course must remain unpublished."
            );

            $courseDescriptions[] = $course->description;

            foreach (self::LESSON_TITLES as $lessonTitle) {
                $lesson = Lesson::query()
                    ->where('title', $lessonTitle)
                    ->whereHas('module.course', function ($query) use ($ageGroup) {
                        $query
                            ->where('age_group', $ageGroup)
                            ->whereHas(
                                'subject',
                                fn($subjectQuery) => $subjectQuery
                                    ->where('slug', 'self-discovery')
                            );
                    })
                    ->firstOrFail();

                $this->assertFalse(
                    (bool) $lesson->is_published,
                    "{$lessonTitle} must remain unpublished."
                );

                $this->assertStringNotContainsString(
                    'Learning guidance:',
                    (string) $lesson->description
                );

                $story = LessonActivity::query()
                    ->where('lesson_id', $lesson->id)
                    ->where('activity_type', 'story')
                    ->firstOrFail();

                $this->assertFalse((bool) $story->is_published);
                $this->assertTrue(
                    $story->skills()->exists(),
                    "{$story->title} must have a skill mapping."
                );

                $storyContent = $story->content;

                $this->assertIsArray($storyContent);
                $this->assertIsString($storyContent['instructions'] ?? null);
                $this->assertNotSame(
                    '',
                    trim($storyContent['instructions'])
                );

                $this->assertStringNotContainsString(
                    'Learning guidance:',
                    $storyContent['instructions']
                );

                $storiesByLesson[$lessonTitle][] =
                    $storyContent['instructions'];

                $quiz = LessonActivity::query()
                    ->where('lesson_id', $lesson->id)
                    ->where('activity_type', 'quiz')
                    ->firstOrFail();

                $this->assertFalse((bool) $quiz->is_published);
                $this->assertTrue(
                    $quiz->skills()->exists(),
                    "{$quiz->title} must have a skill mapping."
                );

                $quizContent = $quiz->content;

                $this->assertIsArray($quizContent);
                $this->assertIsString($quizContent['question'] ?? null);
                $this->assertNotSame(
                    '',
                    trim($quizContent['question'])
                );

                $options = $quizContent['options'] ?? null;

                $this->assertIsArray($options);
                $this->assertGreaterThanOrEqual(2, count($options));

                foreach ($options as $option) {
                    $this->assertIsArray($option);
                    $this->assertIsString($option['option'] ?? null);
                    $this->assertNotSame(
                        '',
                        trim($option['option'])
                    );
                }

                $answer = $quizContent['answer'] ?? null;

                $this->assertIsInt($answer);
                $this->assertArrayHasKey($answer, $options);

                $questionsByLesson[$lessonTitle][] =
                    $quizContent['question'];
            }
        }

        $this->assertCount(
            3,
            array_unique($courseDescriptions),
            'Each age band should have its own course description.'
        );

        foreach (self::LESSON_TITLES as $lessonTitle) {
            $this->assertCount(
                3,
                array_unique($storiesByLesson[$lessonTitle]),
                "{$lessonTitle} should have a distinct story for each age band."
            );

            $this->assertCount(
                3,
                array_unique($questionsByLesson[$lessonTitle]),
                "{$lessonTitle} should have a distinct quiz question for each age band."
            );
        }
    }

    public function test_rerunning_the_seeder_preserves_publication_status_and_counts(): void
    {
        $course = Course::query()
            ->where('age_group', '6-9')
            ->whereHas(
                'subject',
                fn($query) => $query->where('slug', 'self-discovery')
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
                        fn($subjectQuery) => $subjectQuery
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

        $this->assertTrue(
            (bool) $course->fresh()->is_published,
            'Rerunning the seeder must not unpublish a reviewed course.'
        );

        $this->assertTrue(
            (bool) $lesson->fresh()->is_published,
            'Rerunning the seeder must not unpublish a reviewed lesson.'
        );

        $this->assertTrue(
            (bool) $story->fresh()->is_published,
            'Rerunning the seeder must not unpublish a reviewed activity.'
        );

        $this->assertSame(
            3,
            Course::query()
                ->whereHas(
                    'subject',
                    fn($query) => $query->where('slug', 'self-discovery')
                )
                ->count()
        );

        $this->assertSame(
            12,
            Lesson::query()
                ->whereHas(
                    'module.course.subject',
                    fn($query) => $query->where('slug', 'self-discovery')
                )
                ->count()
        );

        $this->assertSame(
            24,
            LessonActivity::query()
                ->whereHas(
                    'lesson.module.course.subject',
                    fn($query) => $query->where('slug', 'self-discovery')
                )
                ->count()
        );
    }

    public function test_growth_habits_has_distinct_age_specific_content(): void
    {
        $lessonTitles = [
            'Choosing a Small Goal',
            'Learning Through Practice',
            'Building a Helpful Habit',
            'Giving a Task My Attention',
        ];

        $storiesByLesson = [];
        $questionsByLesson = [];
        $descriptions = [];

        foreach (self::AGE_GROUPS as $ageGroup) {
            $course = Course::query()
                ->where('age_group', $ageGroup)
                ->whereHas(
                    'subject',
                    fn($query) => $query->where('slug', 'growth-habits')
                )
                ->firstOrFail();

            $this->assertFalse((bool) $course->is_published);
            $descriptions[] = $course->description;

            foreach ($lessonTitles as $lessonTitle) {
                $lesson = Lesson::query()
                    ->where('title', $lessonTitle)
                    ->whereHas('module.course', function ($query) use ($ageGroup) {
                        $query
                            ->where('age_group', $ageGroup)
                            ->whereHas(
                                'subject',
                                fn($subjectQuery) => $subjectQuery
                                    ->where('slug', 'growth-habits')
                            );
                    })
                    ->firstOrFail();

                $this->assertFalse((bool) $lesson->is_published);

                $story = LessonActivity::query()
                    ->where('lesson_id', $lesson->id)
                    ->where('activity_type', 'story')
                    ->firstOrFail();

                $this->assertFalse((bool) $story->is_published);

                $storyText = $story->content['instructions'] ?? null;

                $this->assertIsString($storyText);
                $this->assertNotSame('', trim($storyText));
                $this->assertTrue($story->skills()->exists());

                $storiesByLesson[$lessonTitle][] = $storyText;

                $quiz = LessonActivity::query()
                    ->where('lesson_id', $lesson->id)
                    ->where('activity_type', 'quiz')
                    ->firstOrFail();

                $this->assertFalse((bool) $quiz->is_published);
                $this->assertTrue($quiz->skills()->exists());

                $content = $quiz->content;
                $this->assertIsString($content['question'] ?? null);

                $options = $content['options'] ?? null;
                $this->assertIsArray($options);
                $this->assertGreaterThanOrEqual(2, count($options));

                $answer = $content['answer'] ?? null;
                $this->assertIsInt($answer);
                $this->assertArrayHasKey($answer, $options);

                $questionsByLesson[$lessonTitle][] = $content['question'];
            }
        }

        $this->assertCount(3, array_unique($descriptions));

        foreach ($lessonTitles as $lessonTitle) {
            $this->assertCount(
                3,
                array_unique($storiesByLesson[$lessonTitle]),
                "{$lessonTitle} must have a distinct story for each age band."
            );

            $this->assertCount(
                3,
                array_unique($questionsByLesson[$lessonTitle]),
                "{$lessonTitle} must have a distinct question for each age band."
            );
        }
    }
}
