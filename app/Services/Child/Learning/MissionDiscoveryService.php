<?php

namespace App\Services\Child\Learning;


class MissionDiscoveryService
{
    public function getCategories(): array
    {
        return [
            [
                'name' => 'Science',
                'icon' => '🔬'
            ],

            [
                'name' => 'Technology',
                'icon' => '💻'
            ],

            [
                'name' => 'Creativity',
                'icon' => '🎨'
            ],

            [
                'name' => 'AI',
                'icon' => '🤖'
            ],

            [
                'name' => 'Life Skills',
                'icon' => '🌱'
            ],
        ];
    }



    public function getMissions(
        string $category = 'all'
    ): array {
        $missions = [

            [

                'title' => 'Build A Space Robot',
                'category' => 'Technology',
                'icon' => '🤖',
                'description' => 'Create your first robot design.',
                'xp' => 100,
                'time' => '20 mins',
                'difficulty' => 'Explorer',
            ],


            [

                'title' => 'Solar System Explorer',
                'category' => 'Science',
                'icon' => '🪐',
                'description' => 'Discover planets and galaxies.',
                'xp' => 80,
                'time' => '15 mins',
                'difficulty' => 'Beginner',
            ],


            [

                'title' => 'AI Art Studio',
                'category' => 'AI',
                'icon' => '🎨',
                'description' => 'Create amazing artwork with AI.',
                'xp' => 120,
                'time' => '25 mins',
                'difficulty' => 'Explorer',
            ],


            [

                'title' => 'Story Creator',
                'category' => 'Creativity',
                'icon' => '📖',
                'description' => 'Create your own adventure story.',
                'xp' => 60,
                'time' => '10 mins',
                'difficulty' => 'Beginner',
            ],


        ];

        if ($category === 'all') {
            return $missions;
        }

        return collect($missions)
            ->where('category', $category)
            ->values()
            ->toArray();
    }
}
