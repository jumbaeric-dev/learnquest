<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;
use Illuminate\Support\Str;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [

            [
                'name' => 'First Lesson',
                'description' => 'Completed your very first learning activity.',
                'icon' => 'heroicon-o-academic-cap',
                'xp_reward' => 10,
            ],

            [
                'name' => 'AI Explorer',
                'description' => 'Completed your first AI for Kids lesson.',
                'icon' => 'heroicon-o-cpu-chip',
                'xp_reward' => 25,
            ],

            [
                'name' => 'Prompt Master',
                'description' => 'Successfully learned prompt engineering basics.',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'xp_reward' => 50,
            ],

            [
                'name' => 'Creative Genius',
                'description' => 'Created your first AI-generated artwork.',
                'icon' => 'heroicon-o-paint-brush',
                'xp_reward' => 50,
            ],

            [
                'name' => 'Coding Rookie',
                'description' => 'Completed your first coding challenge.',
                'icon' => 'heroicon-o-code-bracket',
                'xp_reward' => 50,
            ],

            [
                'name' => 'AI Safety Champion',
                'description' => 'Completed all AI Safety activities.',
                'icon' => 'heroicon-o-shield-check',
                'xp_reward' => 75,
            ],

            [
                'name' => 'Future Innovator',
                'description' => 'Completed the entire AI for Kids course.',
                'icon' => 'heroicon-o-light-bulb',
                'xp_reward' => 100,
            ],

            [
                'name' => 'Problem Solver',
                'description' => 'Completed 10 challenge activities.',
                'icon' => 'heroicon-o-puzzle-piece',
                'xp_reward' => 40,
            ],

            [
                'name' => 'Critical Thinker',
                'description' => 'Scored 90% or higher in 5 quizzes.',
                'icon' => 'heroicon-o-brain',
                'xp_reward' => 60,
            ],

            [
                'name' => 'Digital Citizen',
                'description' => 'Learned online safety and responsible technology use.',
                'icon' => 'heroicon-o-globe-alt',
                'xp_reward' => 40,
            ],

            [
                'name' => '7 Day Streak',
                'description' => 'Learned for 7 consecutive days.',
                'icon' => 'heroicon-o-fire',
                'xp_reward' => 70,
            ],

            [
                'name' => 'Math Wizard',
                'description' => 'Completed 20 math activities.',
                'icon' => 'heroicon-o-calculator',
                'xp_reward' => 50,
            ],

            [
                'name' => 'Science Explorer',
                'description' => 'Completed 20 science activities.',
                'icon' => 'heroicon-o-beaker',
                'xp_reward' => 50,
            ],

            [
                'name' => 'Creative Artist',
                'description' => 'Completed 20 art and creativity activities.',
                'icon' => 'heroicon-o-photo',
                'xp_reward' => 50,
            ],

            [
                'name' => 'LearnQuest Legend',
                'description' => 'Reached Level 25.',
                'icon' => 'heroicon-o-trophy',
                'xp_reward' => 250,
            ],

            [
                'name' => '7 Day Streak',
                'slug' => '7-day-streak',
                'description' => 'Learned for 7 days in a row.',
                'icon' => 'heroicon-o-fire',
                'xp_reward' => 50,
            ],

            [
                'name' => '30 Day Streak',
                'slug' => '30-day-streak',
                'description' => 'Learned for 30 days in a row.',
                'icon' => 'heroicon-o-bolt',
                'xp_reward' => 200,
            ],

            [
                'name' => '100 Day Streak',
                'slug' => '100-day-streak',
                'description' => 'Learned for 100 days in a row.',
                'icon' => 'heroicon-o-trophy',
                'xp_reward' => 1000,
            ],

        ];

        foreach ($badges as $badge) {

            Badge::updateOrCreate(
                ['slug' => Str::slug($badge['name'])],
                [
                    'name' => $badge['name'],
                    'slug' => Str::slug($badge['name']),
                    'description' => $badge['description'],
                    'icon' => $badge['icon'],
                    'xp_reward' => $badge['xp_reward'],
                    'is_active' => true,
                ]
            );
        }
    }
}
