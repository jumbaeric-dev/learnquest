<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class MilestoneBadgeSeeder extends Seeder
{
    /**
     * world slug => [explorer name, champion name, icon, label]
     */
    private const WORLD_BADGES = [
        'money-financial-wisdom' => ['Money Explorer', 'Money Champion', 'heroicon-o-banknotes', 'Money'],
        'math-number-sense' => ['Number Explorer', 'Math Champion', 'heroicon-o-calculator', 'Math'],
        'coding-robotics-engineering' => ['Code Explorer', 'Code Champion', 'heroicon-o-code-bracket', 'Coding'],
        'ai-future-intelligence' => ['Curious About AI', 'AI Champion', 'heroicon-o-cpu-chip', 'AI'],
        'self-discovery' => ['Feelings Explorer', 'Heart Hero', 'heroicon-o-heart', 'Self-Discovery'],
        'people-relationships' => ['Kindness Explorer', 'Teamwork Champion', 'heroicon-o-users', 'People and Relationships'],
        'critical-thinking' => ['Thinking Explorer', 'Thinking Champion', 'heroicon-o-light-bulb', 'Critical Thinking'],
        'digital-life-safety' => ['Safety Explorer', 'Safety Champion', 'heroicon-o-shield-check', 'Digital Safety'],
        'science-nature-universe' => ['Nature Explorer', 'Science Champion', 'heroicon-o-beaker', 'Science'],
        'reading-storytelling' => ['Story Explorer', 'Story Champion', 'heroicon-o-book-open', 'Reading and Storytelling'],
    ];

    /**
     * slug => [name, description, icon, xp reward]
     */
    private const GENERAL_BADGES = [
        'rising-star' => ['Rising Star', 'Reached Level 5.', 'heroicon-o-star', 25],
        'quest-hero' => ['Quest Hero', 'Reached Level 10.', 'heroicon-o-flag', 75],
        'streak-starter' => ['Streak Starter', 'Learned for 3 days in a row.', 'heroicon-o-fire', 20],
        'perfect-score' => ['Perfect Score', 'Answered every question right in a check-up.', 'heroicon-o-sparkles', 30],
        'course-finisher' => ['Course Finisher', 'Finished your first full course.', 'heroicon-o-academic-cap', 100],
        'world-traveller' => ['World Traveller', 'Started learning in 5 different worlds.', 'heroicon-o-globe-alt', 75],
    ];

    public function run(): void
    {
        foreach (self::WORLD_BADGES as $world => [$explorer, $champion, $icon, $label]) {
            $this->upsert(
                $world . '-explorer',
                $explorer,
                "Finished your first lesson in the {$label} world.",
                $icon,
                25
            );

            $this->upsert(
                $world . '-champion',
                $champion,
                "Completed a full course in the {$label} world.",
                $icon,
                100
            );
        }

        foreach (self::GENERAL_BADGES as $slug => [$name, $description, $icon, $xp]) {
            $this->upsert($slug, $name, $description, $icon, $xp);
        }

        $this->command?->info(
            'Milestone badges seeded: ' . ((count(self::WORLD_BADGES) * 2) + count(self::GENERAL_BADGES)) . '.'
        );
    }

    private function upsert(string $slug, string $name, string $description, string $icon, int $xp): void
    {
        Badge::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $description,
                'icon' => $icon,
                'xp_reward' => $xp,
                'is_active' => true,
            ]
        );
    }
}