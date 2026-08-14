<?php

namespace App\Livewire\Child\Worlds\Components;

use Livewire\Component;

class AdventurePath extends Component
{
    public array $nodes = [
        [
            'id' => 1,
            'title' => 'Counting Stars',
            'description' => 'Practice counting numbers in the galaxy.',
            'icon' => '⭐',
            'status' => 'completed',
            'xp' => 50,
        ],

        [
            'id' => 2,
            'title' => 'Rocket Maths',
            'description' => 'Solve quick maths challenges to fuel your rocket.',
            'icon' => '🚀',
            'status' => 'completed',
            'xp' => 75,
        ],

        [
            'id' => 3,
            'title' => 'Planet Puzzle',
            'description' => 'Use your maths skills to solve the planetary puzzle.',
            'icon' => '🪐',
            'status' => 'current',
            'xp' => 100,
        ],

        [
            'id' => 4,
            'title' => 'Galaxy Quiz',
            'description' => 'Test what you have learned so far.',
            'icon' => '🌌',
            'status' => 'locked',
            'xp' => 100,
        ],

        [
            'id' => 5,
            'title' => 'Galaxy Master',
            'description' => 'Complete the galaxy adventure.',
            'icon' => '🏆',
            'status' => 'reward',
            'xp' => 250,
        ],
    ];

    public function startNode(int $id): void
    {
        $node = collect($this->nodes)
            ->firstWhere('id', $id);

        if (!$node || $node['status'] === 'locked') {
            return;
        }

        $this->dispatch(
            'adventure-node-selected',
            nodeId: $id
        );
    }

    public function render()
    {
        return view(
            'livewire.child.worlds.components.adventure-path'
        );
    }
}