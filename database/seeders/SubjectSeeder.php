<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * The canonical LearnQuest worlds.
     *
     * These slugs must remain consistent with
     * LearnQuestCurriculumSeeder.
     */
    private const WORLDS = [
        [
            'name' => 'Self-Discovery',
            'slug' => 'self-discovery',
            'description' => 'Discover personal identity, strengths, values, emotions, and the qualities that make each learner unique.',
            'icon' => 'compass',
            'theme_color' => '#8B5CF6',
            'sort_order' => 1,
            'difficulty' => 'beginner',
            'tagline' => 'Discover what makes you, you.',
            'story_intro' => 'Begin an adventure of self-discovery. Explore your strengths, understand your feelings, and learn what matters to you.',
            'hero_character' => 'Nova',
            'banner_animation' => 'sparkle',
            'unlock_level' => 1,
            'estimated_hours' => 8,
            'badge_icon' => 'star',
            'is_featured' => true,
            'is_seasonal' => false,
        ],
        [
            'name' => 'Growth and Habits',
            'slug' => 'growth-habits',
            'description' => 'Build helpful routines, practise self-regulation, set goals, manage time, and develop resilience.',
            'icon' => 'sprout',
            'theme_color' => '#22C55E',
            'sort_order' => 2,
            'difficulty' => 'beginner',
            'tagline' => 'Small steps lead to big growth.',
            'story_intro' => 'Practise one small step at a time. Learn how goals, helpful habits, and persistence can support your growth.',
            'hero_character' => 'Pip',
            'banner_animation' => 'grow',
            'unlock_level' => 1,
            'estimated_hours' => 10,
            'badge_icon' => 'sprout',
            'is_featured' => true,
            'is_seasonal' => false,
        ],
        [
            'name' => 'People and Relationships',
            'slug' => 'people-relationships',
            'description' => 'Develop empathy, communication, teamwork, healthy boundaries, and constructive conflict resolution.',
            'icon' => 'users',
            'theme_color' => '#F472B6',
            'sort_order' => 3,
            'difficulty' => 'beginner',
            'tagline' => 'Understand others and grow together.',
            'story_intro' => 'Explore how people communicate, cooperate, respect boundaries, and work through disagreements.',
            'hero_character' => 'Milo',
            'banner_animation' => 'float',
            'unlock_level' => 1,
            'estimated_hours' => 10,
            'badge_icon' => 'heart',
            'is_featured' => true,
            'is_seasonal' => false,
        ],
        [
            'name' => 'Critical Thinking',
            'slug' => 'critical-thinking',
            'description' => 'Ask useful questions, evaluate information, solve problems, make thoughtful decisions, and explore ideas.',
            'icon' => 'brain',
            'theme_color' => '#F59E0B',
            'sort_order' => 4,
            'difficulty' => 'beginner',
            'tagline' => 'Ask questions. Explore possibilities.',
            'story_intro' => 'Become a curious investigator. Gather clues, compare ideas, and practise explaining why you believe something.',
            'hero_character' => 'Quill',
            'banner_animation' => 'pulse',
            'unlock_level' => 1,
            'estimated_hours' => 12,
            'badge_icon' => 'lightbulb',
            'is_featured' => true,
            'is_seasonal' => false,
        ],
        [
            'name' => 'Digital Life and Safety',
            'slug' => 'digital-life-safety',
            'description' => 'Learn digital literacy, online privacy, safer technology habits, responsible participation, and media awareness.',
            'icon' => 'shield-check',
            'theme_color' => '#3B82F6',
            'sort_order' => 5,
            'difficulty' => 'beginner',
            'tagline' => 'Be thoughtful, safe, and responsible online.',
            'story_intro' => 'Explore digital spaces and practise protecting private information, checking online claims, and seeking help when needed.',
            'hero_character' => 'Byte',
            'banner_animation' => 'scan',
            'unlock_level' => 1,
            'estimated_hours' => 10,
            'badge_icon' => 'shield-check',
            'is_featured' => true,
            'is_seasonal' => false,
        ],
        [
            'name' => 'Money and Financial Wisdom',
            'slug' => 'money-financial-wisdom',
            'description' => 'Understand money, needs and wants, saving, budgeting, spending choices, and financial responsibility.',
            'icon' => 'wallet',
            'theme_color' => '#14B8A6',
            'sort_order' => 6,
            'difficulty' => 'beginner',
            'tagline' => 'Make thoughtful choices with money.',
            'story_intro' => 'Explore everyday money decisions through age-appropriate examples about needs, wants, saving, and planning.',
            'hero_character' => 'Penny',
            'banner_animation' => 'bounce',
            'unlock_level' => 1,
            'estimated_hours' => 12,
            'badge_icon' => 'wallet',
            'is_featured' => true,
            'is_seasonal' => false,
        ],
        [
            'name' => 'Entrepreneurship and Innovation',
            'slug' => 'entrepreneurship-innovation',
            'description' => 'Notice opportunities, understand needs, create value, test ideas, plan projects, and act responsibly.',
            'icon' => 'lightbulb',
            'theme_color' => '#F97316',
            'sort_order' => 7,
            'difficulty' => 'beginner',
            'tagline' => 'Turn thoughtful ideas into useful action.',
            'story_intro' => 'Find everyday problems, imagine possible solutions, and learn how to test ideas that help people.',
            'hero_character' => 'Spark',
            'banner_animation' => 'spark',
            'unlock_level' => 1,
            'estimated_hours' => 12,
            'badge_icon' => 'lightbulb',
            'is_featured' => false,
            'is_seasonal' => false,
        ],
        [
            'name' => 'AI and Future Intelligence',
            'slug' => 'ai-future-intelligence',
            'description' => 'Build age-appropriate AI literacy, data awareness, understanding of AI limitations, and responsible AI habits.',
            'icon' => 'bot',
            'theme_color' => '#6366F1',
            'sort_order' => 8,
            'difficulty' => 'beginner',
            'tagline' => 'Understand intelligent tools and use them wisely.',
            'story_intro' => 'Discover how AI tools can help with some tasks, where they can make mistakes, and why human judgement matters.',
            'hero_character' => 'Nova',
            'banner_animation' => 'glow',
            'unlock_level' => 1,
            'estimated_hours' => 10,
            'badge_icon' => 'bot',
            'is_featured' => false,
            'is_seasonal' => false,
        ],
        [
            'name' => 'Coding, Robotics and Engineering',
            'slug' => 'coding-robotics-engineering',
            'description' => 'Practise computational thinking, sequencing, coding fundamentals, debugging, engineering design, and robotics concepts.',
            'icon' => 'code',
            'theme_color' => '#0EA5E9',
            'sort_order' => 9,
            'difficulty' => 'beginner',
            'tagline' => 'Imagine, build, test, and improve.',
            'story_intro' => 'Learn to break challenges into steps, give clear instructions, test solutions, and improve designs.',
            'hero_character' => 'Rivet',
            'banner_animation' => 'assemble',
            'unlock_level' => 1,
            'estimated_hours' => 16,
            'badge_icon' => 'code',
            'is_featured' => false,
            'is_seasonal' => false,
        ],
        [
            'name' => 'Science, Nature and the Universe',
            'slug' => 'science-nature-universe',
            'description' => 'Explore scientific inquiry, observation, measurement, evidence, environmental awareness, and connected systems.',
            'icon' => 'telescope',
            'theme_color' => '#10B981',
            'sort_order' => 10,
            'difficulty' => 'beginner',
            'tagline' => 'Explore the world and ask how it works.',
            'story_intro' => 'Observe the world around you, ask questions, look for evidence, and explore how living and non-living systems work.',
            'hero_character' => 'Orbit',
            'banner_animation' => 'orbit',
            'unlock_level' => 1,
            'estimated_hours' => 16,
            'badge_icon' => 'telescope',
            'is_featured' => false,
            'is_seasonal' => false,
        ],
        [
            'name' => 'Healthy and Practical Life',
            'slug' => 'healthy-practical-life',
            'description' => 'Practise everyday hygiene, healthy routines, personal safety awareness, practical skills, and help-seeking.',
            'icon' => 'heart-pulse',
            'theme_color' => '#EF4444',
            'sort_order' => 11,
            'difficulty' => 'beginner',
            'tagline' => 'Care for yourself and know when to ask for help.',
            'story_intro' => 'Explore everyday habits that support wellbeing, practical independence, personal safety, and asking trusted people for help.',
            'hero_character' => 'Sunny',
            'banner_animation' => 'breathe',
            'unlock_level' => 1,
            'estimated_hours' => 12,
            'badge_icon' => 'heart',
            'is_featured' => false,
            'is_seasonal' => false,
        ],
        [
            'name' => 'Leadership, Citizenship and Global Futures',
            'slug' => 'leadership-citizenship-global-futures',
            'description' => 'Develop responsibility, inclusion, civic awareness, community participation, leadership, and global understanding.',
            'icon' => 'globe',
            'theme_color' => '#8B5CF6',
            'sort_order' => 12,
            'difficulty' => 'beginner',
            'tagline' => 'Make a positive difference together.',
            'story_intro' => 'Discover how people contribute to communities, treat others fairly, share responsibility, and care for our connected world.',
            'hero_character' => 'Atlas',
            'banner_animation' => 'float',
            'unlock_level' => 1,
            'estimated_hours' => 12,
            'badge_icon' => 'globe',
            'is_featured' => false,
            'is_seasonal' => false,
        ],
    ];


    public function run(): void
    {
        foreach (self::WORLDS as $world) {
            $slug = $world['slug'];

            Subject::updateOrCreate(
                ['slug' => $slug],
                array_merge($world, [
                    'is_active' => true,
                ])
            );
        }

        // Preserve legacy worlds used by existing parts of LearnQuest.
        // Create missing records and fill blank icons without overwriting
        // custom icons on records that already exist.
        $legacyWorlds = [
            'math-galaxy' => [
                'name' => 'Math Galaxy',
                'icon' => 'heroicon-o-academic-cap',
            ],
            'reading-kingdom' => [
                'name' => 'Reading Kingdom',
                'icon' => 'heroicon-o-book-open',
            ],
            'ai-academy' => [
                'name' => 'AI Academy',
                'icon' => 'heroicon-o-sparkles',
            ],
        ];

        foreach ($legacyWorlds as $slug => $defaults) {
            $subject = Subject::firstOrCreate(
                ['slug' => $slug],
                array_merge($defaults, [
                    'is_active' => false,
                ])
            );

            $changed = false;

            // Keep the record and any custom icon, but archive the world.
            if ($subject->is_active) {
                $subject->is_active = false;
                $changed = true;
            }

            if (blank($subject->icon)) {
                $subject->icon = $defaults['icon'];
                $changed = true;
            }

            if ($changed) {
                $subject->save();
            }
        }

        // The canonical 12 worlds are the only active learning worlds.
        $canonicalSlugs = array_column(self::WORLDS, 'slug');

        Subject::query()
            ->whereNotIn('slug', $canonicalSlugs)
            ->update(['is_active' => false]);

        $this->command?->info(
            'LearnQuest canonical worlds seeded: '
                . count(self::WORLDS)
                . '. Non-canonical worlds are inactive and preserved.'
        );
    }
}
