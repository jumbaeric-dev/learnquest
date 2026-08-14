<?php

namespace App\Livewire\Child\Missions\Components;

use Livewire\Component;

class FeaturedMission extends Component
{
    public array $mission = [
        'icon' => '🪐',
        'title' => 'Build A Space Robot',
        'category' => 'Science + Coding',
        'description' =>
            'Design your own robot that can explore distant planets.',
        'xp' => 100,
        'time' => '20 mins',
        'difficulty' => 'Explorer',
    ];

    public function startMission()
    {
        // Future:
        // Redirect child into mission journey

        return $this->redirect(
    route(
        'child.mission.show',
        'math-galaxy'
    )
);
    }

    public function render()
    {
        return view(
            'livewire.child.missions.components.featured-mission'
        );
    }

}