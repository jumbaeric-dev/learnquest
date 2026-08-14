<?php

namespace App\Livewire\Child\Explore\Components;

use Livewire\Component;

class LearningWorlds extends Component
{
    public array $worlds = [];


    public function mount(): void
    {
        $this->worlds = [

            [
                'title' => 'Reading Kingdom',
                'description' => 'Discover stories and unlock new words.',
                'icon' => '📚',
                'progress' => 45,
                'xp' => 120,
                'theme' => 'blue',
                'locked' => false,
                'badge' => 'Adventure',
                'slug' => 'reading-kingdom',
            ],


            [
                'title' => 'Math Galaxy',
                'description' => 'Explore numbers, puzzles and challenges.',
                'icon' => '🌌',
                'progress' => 68,
                'xp' => 150,
                'theme' => 'purple',
                'locked' => false,
                'badge' => 'Popular',
                'slug' => 'math-galaxy',
            ],


            [
                'title' => 'Science Lab',
                'description' => 'Experiment and discover amazing things.',
                'icon' => '🔬',
                'progress' => 20,
                'xp' => 200,
                'theme' => 'green',
                'locked' => false,
                'badge' => 'New',
                'slug' => 'science-lab',
            ],


            [
                'title' => 'Coding Planet',
                'description' => 'Create games and build technology.',
                'icon' => '💻',
                'progress' => 0,
                'xp' => 250,
                'theme' => 'orange',
                'locked' => false,
                'badge' => 'Future',
                'slug' => 'coding-planet',
            ],


            [
                'title' => 'AI Universe',
                'description' => 'Discover artificial intelligence.',
                'icon' => '🤖',
                'progress' => 0,
                'xp' => 300,
                'theme' => 'pink',
                'locked' => true,
                'badge' => 'Coming Soon',
                'slug' => 'ai-universe',
            ],

        ];
    }


    public function openWorld(string $title): void
    {
        // Future:
        // Navigate to world
        // Load courses
        // Start journey

    }


    public function render()
    {
        return view(
            'livewire.child.explore.components.learning-worlds'
        );
    }
}