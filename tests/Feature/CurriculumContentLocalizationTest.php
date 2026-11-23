<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LessonActivity;
use App\Models\Lesson;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurriculumContentLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('fr');
    }

    public function test_world_course_and_lesson_use_french_copy_when_available(): void
    {
        $world = new Subject([
            'name' => 'Money & Financial Wisdom',
            'description' => 'Learn about money.',
            'translations' => [
                'fr' => [
                    'name' => 'Argent et sagesse financière',
                    'description' => 'Apprends à gérer ton argent.',
                ],
            ],
        ]);

        $course = new Course([
            'title' => 'My First Budget',
            'translations' => ['fr' => ['title' => 'Mon premier budget']],
        ]);

        $lesson = new Lesson([
            'title' => 'Needs and Wants',
            'translations' => ['fr' => ['title' => 'Besoins et envies']],
        ]);

        $this->assertSame('Argent et sagesse financière', $world->localized('name'));
        $this->assertSame('Apprends à gérer ton argent.', $world->localized('description'));
        $this->assertSame('Mon premier budget', $course->localized('title'));
        $this->assertSame('Besoins et envies', $lesson->localized('title'));
    }

    public function test_missing_translation_falls_back_to_canonical_content(): void
    {
        $course = new Course([
            'title' => 'My First Budget',
            'translations' => ['fr' => ['description' => 'Description française']],
        ]);

        $this->assertSame('My First Budget', $course->localized('title'));
    }

    public function test_activity_content_translates_learner_copy_but_preserves_quiz_answer(): void
    {
        $activity = new LessonActivity([
            'title' => 'Choose the safe choice',
            'content' => [
                'question' => 'Which choice is safe?',
                'options' => [
                    ['option' => 'Tell a trusted adult'],
                    ['option' => 'Go with a stranger'],
                ],
                'answer' => 0,
                'story_text' => 'A short English story.',
            ],
            'translations' => [
                'fr' => [
                    'title' => 'Choisis la bonne décision',
                    'content' => [
                        'question' => 'Quel choix est sûr ?',
                        'options' => [
                            ['option' => 'Parler à un adulte de confiance'],
                            ['option' => 'Partir avec un inconnu'],
                        ],
                        // Even if bad or stale translation data contains an
                        // answer field, it must never override canonical scoring.
                        'answer' => 1,
                        'story_text' => 'Une courte histoire en français.',
                    ],
                ],
            ],
        ]);

        $content = $activity->localizedContent();

        $this->assertSame('Choisis la bonne décision', $activity->localized('title'));
        $this->assertSame('Quel choix est sûr ?', $content['question']);
        $this->assertSame('Parler à un adulte de confiance', $content['options'][0]['option']);
        $this->assertSame(0, $content['answer']);
        $this->assertSame('Une courte histoire en français.', $content['story_text']);
    }

    public function test_activity_without_translation_uses_original_content(): void
    {
        $activity = new LessonActivity([
            'content' => ['question' => 'What is a budget?', 'answer' => 1],
        ]);

        $this->assertSame('What is a budget?', $activity->localizedContent()['question']);
        $this->assertSame(1, $activity->localizedContent()['answer']);
    }

    public function test_activity_translation_cannot_add_a_new_quiz_question_to_non_quiz_content(): void
    {
        $activity = new LessonActivity([
            'activity_type' => 'story',
            'content' => ['story_text' => 'Read this story.'],
            'translations' => [
                'fr' => [
                    'content' => ['question' => 'Question added only by translation?'],
                ],
            ],
        ]);

        $content = $activity->localizedContent();

        $this->assertArrayNotHasKey('question', $content);
        $this->assertSame('Read this story.', $content['story_text']);
    }

    public function test_curriculum_tables_have_translation_storage(): void
    {
        foreach (['subjects', 'courses', 'learning_modules', 'lessons', 'activities'] as $table) {
            $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn($table, 'translations'));
        }
    }
}
