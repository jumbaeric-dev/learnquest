<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [

            // Technology
            [
                'name' => 'AI Literacy',
                'description' => 'Understanding artificial intelligence, how it works, and how to use it responsibly.',
                'icon' => 'heroicon-o-cpu-chip',
            ],
            [
                'name' => 'Digital Literacy',
                'description' => 'Using digital tools safely and effectively.',
                'icon' => 'heroicon-o-device-phone-mobile',
            ],
            [
                'name' => 'Coding',
                'description' => 'Understanding programming logic and software creation.',
                'icon' => 'heroicon-o-code-bracket',
            ],

            // Cognitive Skills
            [
                'name' => 'Critical Thinking',
                'description' => 'Analyzing information and making reasoned decisions.',
                'icon' => 'heroicon-o-light-bulb',
            ],
            [
                'name' => 'Problem Solving',
                'description' => 'Finding creative and effective solutions to challenges.',
                'icon' => 'heroicon-o-puzzle-piece',
            ],
            [
                'name' => 'Creativity',
                'description' => 'Generating original ideas and expressing imagination.',
                'icon' => 'heroicon-o-sparkles',
            ],

            // Communication Skills
            [
                'name' => 'Communication',
                'description' => 'Expressing ideas clearly through speaking and writing.',
                'icon' => 'heroicon-o-chat-bubble-left-right',
            ],
            [
                'name' => 'Collaboration',
                'description' => 'Working effectively with others toward shared goals.',
                'icon' => 'heroicon-o-user-group',
            ],
            [
                'name' => 'Leadership',
                'description' => 'Guiding, motivating, and supporting others.',
                'icon' => 'heroicon-o-users',
            ],

            // Future Success Skills
            [
                'name' => 'Entrepreneurship',
                'description' => 'Identifying opportunities and creating value.',
                'icon' => 'heroicon-o-rocket-launch',
            ],
            [
                'name' => 'Financial Literacy',
                'description' => 'Understanding money, saving, budgeting, and investing.',
                'icon' => 'heroicon-o-banknotes',
            ],
            [
                'name' => 'Adaptability',
                'description' => 'Adjusting successfully to change and new situations.',
                'icon' => 'heroicon-o-arrow-path',
            ],
        ];

        foreach ($skills as $skill) {

            Skill::firstOrCreate(
                [
                    'slug' => Str::slug($skill['name']),
                ],
                [
                    'name' => $skill['name'],
                    'description' => $skill['description'],
                    'icon' => $skill['icon'],
                    'is_active' => true,
                ]
            );
        }
    }
}                