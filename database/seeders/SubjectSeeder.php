<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $worlds = [

            [
                'name' => 'Math Galaxy',
                'tagline' => 'Every number unlocks a new star.',
                'description' => 'Master mathematics through exciting space adventures.',
                'story_intro' => 'The stars across Math Galaxy are fading. Solve puzzles and complete missions to restore their light.',
                'hero_character' => 'Professor Pi',
                'difficulty' => 'beginner',
                'theme_color' => '#2563EB',
                'badge_icon' => 'heroicon-o-calculator',
                'estimated_hours' => 20,
                'unlock_level' => 1,
                'sort_order' => 1,
                'is_featured' => true,
            ],

            [
                'name' => 'Reading Kingdom',
                'tagline' => 'Every page opens a new adventure.',
                'description' => 'Discover magical stories while building reading skills.',
                'story_intro' => 'Story Owl needs your help restoring enchanted books scattered throughout the kingdom.',
                'hero_character' => 'Story Owl',
                'difficulty' => 'beginner',
                'theme_color' => '#16A34A',
                'badge_icon' => 'heroicon-o-book-open',
                'estimated_hours' => 18,
                'unlock_level' => 1,
                'sort_order' => 2,
                'is_featured' => true,
            ],

            [
                'name' => 'Science Lab',
                'tagline' => 'Experiment. Discover. Explore.',
                'description' => 'Investigate the amazing world of science.',
                'story_intro' => 'Dr. Spark has exciting experiments waiting for curious explorers.',
                'hero_character' => 'Dr. Spark',
                'difficulty' => 'beginner',
                'theme_color' => '#0891B2',
                'badge_icon' => 'heroicon-o-beaker',
                'estimated_hours' => 22,
                'unlock_level' => 1,
                'sort_order' => 3,
                'is_featured' => true,
            ],

            [
                'name' => 'Code City',
                'tagline' => 'Build the future one line at a time.',
                'description' => 'Learn programming through games and interactive challenges.',
                'story_intro' => 'The robots of Code City need your help fixing their programs.',
                'hero_character' => 'Byte Bot',
                'difficulty' => 'intermediate',
                'theme_color' => '#4F46E5',
                'badge_icon' => 'heroicon-o-code-bracket',
                'estimated_hours' => 30,
                'unlock_level' => 3,
                'sort_order' => 4,
            ],

            [
                'name' => 'Creative Studio',
                'tagline' => 'Imagine. Create. Inspire.',
                'description' => 'Express yourself through art, music and creativity.',
                'story_intro' => 'Pixel Panda is collecting masterpieces from young artists around the world.',
                'hero_character' => 'Pixel Panda',
                'difficulty' => 'beginner',
                'theme_color' => '#EC4899',
                'badge_icon' => 'heroicon-o-paint-brush',
                'estimated_hours' => 15,
                'unlock_level' => 1,
                'sort_order' => 5,
            ],

            [
                'name' => 'Bible Adventures',
                'tagline' => 'Journey through timeless stories.',
                'description' => 'Explore inspiring Bible stories and values.',
                'story_intro' => 'Travel with Shepherd Eli and meet heroes of faith through exciting adventures.',
                'hero_character' => 'Shepherd Eli',
                'difficulty' => 'beginner',
                'theme_color' => '#CA8A04',
                'badge_icon' => 'heroicon-o-star',
                'estimated_hours' => 16,
                'unlock_level' => 1,
                'sort_order' => 6,
            ],

            [
                'name' => 'Life Skills Village',
                'tagline' => 'Skills for everyday heroes.',
                'description' => 'Learn confidence, kindness, leadership and responsibility.',
                'story_intro' => 'Captain Kind is helping every explorer become a hero in everyday life.',
                'hero_character' => 'Captain Kind',
                'difficulty' => 'beginner',
                'theme_color' => '#EA580C',
                'badge_icon' => 'heroicon-o-heart',
                'estimated_hours' => 14,
                'unlock_level' => 1,
                'sort_order' => 7,
            ],

            [
                'name' => 'AI Academy',
                'tagline' => 'Build the future with Artificial Intelligence.',
                'description' => 'Discover AI through fun projects and interactive learning.',
                'story_intro' => 'Nova has discovered friendly robots that have forgotten how to learn. Help repair them by mastering Artificial Intelligence.',
                'hero_character' => 'Nova',
                'difficulty' => 'beginner',
                'theme_color' => '#7C3AED',
                'badge_icon' => 'heroicon-o-sparkles',
                'estimated_hours' => 24,
                'unlock_level' => 1,
                'sort_order' => 8,
                'is_featured' => true,
            ],

        ];

        foreach ($worlds as $world) {

            Subject::updateOrCreate(

                [
                    'slug' => Str::slug($world['name']),
                ],

                [
                    'name' => $world['name'],
                    'description' => $world['description'],
                    'tagline' => $world['tagline'],
                    'story_intro' => $world['story_intro'],
                    'hero_character' => $world['hero_character'],

                    'icon' => null,
                    'cover_image' => null,
                    'banner_animation' => null,
                    'background_music' => null,

                    'theme_color' => $world['theme_color'],
                    'difficulty' => $world['difficulty'],
                    'unlock_level' => $world['unlock_level'],
                    'estimated_hours' => $world['estimated_hours'],
                    'sort_order' => $world['sort_order'],

                    'badge_icon' => $world['badge_icon'],

                    'is_featured' => $world['is_featured'] ?? false,
                    'is_seasonal' => false,
                    'is_active' => true,
                ]
            );
        }
    }
}