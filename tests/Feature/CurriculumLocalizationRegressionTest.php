<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CurriculumLocalizationRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_curriculum_models_resolve_translated_fields_and_keep_canonical_values(): void
    {
        $cases = [
            [Subject::class, 'name', 'English world', 'Monde français'],
            [Course::class, 'title', 'English course', 'Cours français'],
            [CourseModule::class, 'title', 'English module', 'Module français'],
            [Lesson::class, 'title', 'English lesson', 'Leçon française'],
        ];

        foreach ($cases as [$modelClass, $field, $canonical, $translated]) {
            $model = new $modelClass([
                $field => $canonical,
                'translations' => ['fr' => [$field => $translated]],
            ]);

            $this->assertSame($translated, $model->localized($field, 'fr'), $modelClass);
            $this->assertSame($canonical, $model->getAttribute($field), $modelClass);
            $this->assertSame($canonical, $model->localized($field, 'en'), $modelClass);
        }
    }

    public function test_missing_locale_and_blank_translation_fall_back_to_english(): void
    {
        $course = new Course([
            'title' => 'My First Budget',
            'description' => 'Learn to plan spending.',
            'translations' => [
                'fr' => [
                    'title' => '  ',
                    'description' => null,
                ],
            ],
        ]);

        $this->assertSame('My First Budget', $course->localized('title', 'fr'));
        $this->assertSame('Learn to plan spending.', $course->localized('description', 'fr'));
        $this->assertSame('My First Budget', $course->localized('title', 'sw'));
    }

    public function test_locale_argument_does_not_depend_on_the_current_application_locale(): void
    {
        app()->setLocale('en');

        $lesson = new Lesson([
            'title' => 'Needs and Wants',
            'translations' => ['fr' => ['title' => 'Besoins et envies']],
        ]);

        $this->assertSame('Besoins et envies', $lesson->localized('title', 'fr'));
        $this->assertSame('Needs and Wants', $lesson->localized('title', 'en'));
        $this->assertSame('Needs and Wants', $lesson->localized('title'));
    }

    public function test_quiz_translation_preserves_answer_index_and_non_copy_settings(): void
    {
        $activity = new LessonActivity([
            'activity_type' => 'quiz',
            'title' => 'Choose the safe option',
            'content' => [
                'question' => 'Which choice is safe?',
                'options' => [
                    ['option' => 'Tell a trusted adult'],
                    ['option' => 'Go with a stranger'],
                ],
                'answer' => 0,
                'attempt_limit' => 2,
                'passing_score' => 70,
            ],
            'translations' => [
                'fr' => [
                    'title' => 'Choisis la bonne option',
                    'content' => [
                        'question' => 'Quel choix est sûr ?',
                        'options' => [
                            ['option' => 'Parler à un adulte de confiance'],
                            ['option' => 'Partir avec un inconnu'],
                        ],
                        'answer' => 1,
                        'attempt_limit' => 99,
                        'passing_score' => 0,
                    ],
                ],
            ],
        ]);

        $content = $activity->localizedContent('fr');

        $this->assertSame('Choisis la bonne option', $activity->localized('title', 'fr'));
        $this->assertSame('Quel choix est sûr ?', $content['question']);
        $this->assertSame('Parler à un adulte de confiance', $content['options'][0]['option']);
        $this->assertSame('Partir avec un inconnu', $content['options'][1]['option']);
        $this->assertSame(0, $content['answer']);
        $this->assertSame(2, $content['attempt_limit']);
        $this->assertSame(70, $content['passing_score']);
    }

    public function test_partial_option_translation_keeps_untranslated_options_and_ignores_extra_options(): void
    {
        $activity = new LessonActivity([
            'activity_type' => 'quiz',
            'content' => [
                'question' => 'What should you do?',
                'options' => [
                    ['option' => 'Ask for help'],
                    ['option' => 'Keep it secret'],
                ],
                'answer' => 0,
            ],
            'translations' => [
                'fr' => [
                    'content' => [
                        'options' => [
                            ['option' => 'Demander de l’aide'],
                            ['option' => '  '],
                            ['option' => 'Option ajoutée'],
                        ],
                    ],
                ],
            ],
        ]);

        $content = $activity->localizedContent('fr');

        $this->assertSame('Demander de l’aide', $content['options'][0]['option']);
        $this->assertSame('Keep it secret', $content['options'][1]['option']);
        $this->assertCount(2, $content['options']);
        $this->assertSame(0, $content['answer']);
        $this->assertSame('What should you do?', $content['question']);
    }

    public function test_activity_translation_cannot_add_or_change_unapproved_content_fields(): void
    {
        $activity = new LessonActivity([
            'activity_type' => 'story',
            'content' => [
                'instructions' => 'Read the story.',
                'story_text' => 'A learner asks a trusted adult for help.',
                'media_url' => 'https://example.test/story.mp4',
                'answer' => 3,
            ],
            'translations' => [
                'fr' => [
                    'content' => [
                        'instructions' => 'Lis cette histoire.',
                        'story_text' => 'Un enfant demande de l’aide à un adulte de confiance.',
                        'media_url' => 'https://example.test/changed.mp4',
                        'answer' => 0,
                        'question' => 'Question ajoutée',
                        'new_setting' => true,
                    ],
                ],
            ],
        ]);

        $content = $activity->localizedContent('fr');

        $this->assertSame('Lis cette histoire.', $content['instructions']);
        $this->assertSame('Un enfant demande de l’aide à un adulte de confiance.', $content['story_text']);
        $this->assertSame('https://example.test/story.mp4', $content['media_url']);
        $this->assertSame(3, $content['answer']);
        $this->assertArrayNotHasKey('question', $content);
        $this->assertArrayNotHasKey('new_setting', $content);
    }

    public function test_activity_without_french_copy_uses_canonical_content(): void
    {
        $activity = new LessonActivity([
            'title' => 'Explore the idea',
            'activity_type' => 'story',
            'content' => ['instructions' => 'Read and think about the story.'],
            'translations' => ['fr' => ['title' => '  ', 'content' => []]],
        ]);

        $this->assertSame('Explore the idea', $activity->localized('title', 'fr'));
        $this->assertSame(
            ['instructions' => 'Read and think about the story.'],
            $activity->localizedContent('fr'),
        );
    }

    public function test_translation_json_columns_exist_on_every_curriculum_table(): void
    {
        foreach (['subjects', 'courses', 'learning_modules', 'lessons', 'activities'] as $table) {
            $this->assertTrue(
                Schema::hasColumn($table, 'translations'),
                "The {$table}.translations column must exist.",
            );
        }
    }
}
