<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_browser_language_selects_french_when_supported(): void
    {
        $this->withHeaders(['Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8'])
            ->get('/')
            ->assertOk();

        $this->assertSame('fr', app()->getLocale());
    }

    public function test_country_header_selects_french_only_when_detection_is_enabled(): void
    {
        config(['localization.country_detection.enabled' => true]);

        $this->withHeaders(['CF-IPCountry' => 'FR'])
            ->get('/')
            ->assertOk();

        $this->assertSame('fr', app()->getLocale());
    }

    public function test_unsupported_browser_language_falls_back_to_english(): void
    {
        $this->withHeaders(['Accept-Language' => 'ja-JP,ja;q=0.9'])
            ->get('/')
            ->assertOk();

        $this->assertSame('en', app()->getLocale());
    }

    public function test_guest_can_change_language_and_preference_is_saved_in_session(): void
    {
        $this->from('/')->post(route('locale.update'), ['locale' => 'fr'])
            ->assertRedirect('/');

        $this->assertSame('fr', session('locale'));
    }

    public function test_authenticated_user_language_preference_is_persisted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/')->post(route('locale.update'), ['locale' => 'fr'])
            ->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => 'fr',
        ]);
    }

    public function test_child_learning_interface_translations_exist_in_english_and_french(): void
    {
        $keys = [
            'activities',
            'activities_completed_count',
            'activities_on_the_way',
            'activity_complete',
            'adventure_map',
            'ages',
            'back_to_adventure_map',
            'back_to_course',
            'back_to_lesson',
            'chat_with_ai',
            'coding_challenge',
            'complete_activity',
            'complete_activity_prompt',
            'complete_each_lesson',
            'complete_percent',
            'complete_previous_activity',
            'completed',
            'completed_great_job',
            'continue_journey',
            'continue_learning',
            'continue_where_left',
            'course_age_group',
            'courses_count_progress',
            'difficulty',
            'explore',
            'finish_activity_reward',
            'finish_every_activity',
            'keep_exploring',
            'learning_worlds',
            'lesson_complete',
            'lesson_completed_amazing',
            'lesson_finish_message',
            'lesson_xp',
            'lessons',
            'lessons_completed_count',
            'modules',
            'more_adventures',
            'need_score_retry',
            'no_activities_yet',
            'percent_complete',
            'project',
            'question',
            'quiz',
            'recommended_adventures',
            'review',
            'reward',
            'score',
            'score_threshold',
            'start',
            'start_first_course',
            'story',
            'total_xp',
            'try_again',
            'up_next',
            'video',
            'world_courses',
            'world_difficulty',
            'world_guide',
            'world_progress',
            'xp_earned',
            'your_progress',
            'your_score'
        ];

        foreach (['en', 'fr'] as $locale) {
            app()->setLocale($locale);

            foreach ($keys as $key) {
                $this->assertNotSame(
                    "localization.{$key}",
                    __("localization.{$key}"),
                    "Missing {$locale} translation for localization.{$key}",
                );
            }
        }
    }

    public function test_all_child_interface_translation_references_exist_in_english_and_french(): void
    {
        $directories = [
            resource_path('views/livewire/child'),
            resource_path('views/components/child'),
            app_path('Livewire/Child'),
            app_path('Services/Child/Dashboard'),
            app_path('Services/Child/Worlds'),
        ];

        $references = [
            'challenge_complete_lesson_title',
            'challenge_complete_lesson_description',
            'challenge_create_something_title',
            'challenge_create_something_description',
            'challenge_ask_nova_title',
            'challenge_ask_nova_description',
            'beginner',
            'intermediate',
            'advanced',
        ];

        foreach ($directories as $directory) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                if (! $file->isFile() || ! in_array($file->getExtension(), ['php'], true) || str_ends_with($file->getFilename(), '.backup')) {
                    continue;
                }

                preg_match_all('/localization\.([A-Za-z0-9_]+)/', file_get_contents($file->getPathname()), $matches);
                $references = array_merge($references, $matches[1]);
            }
        }

        foreach (['en', 'fr'] as $locale) {
            app()->setLocale($locale);

            foreach (array_unique($references) as $key) {
                $this->assertNotSame(
                    "localization.{$key}",
                    __("localization.{$key}"),
                    "Missing {$locale} translation for localization.{$key}",
                );
            }
        }
    }

    public function test_users_cannot_select_unsupported_locales(): void
    {
        $this->from('/')->post(route('locale.update'), ['locale' => 'xx'])
            ->assertSessionHasErrors('locale');
    }
}
