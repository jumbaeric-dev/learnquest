<?php

namespace App\Livewire\Child\Explore\Components;

use Livewire\Component;

class FeaturedAdventure extends Component
{

    public array $adventure;


    public function mount()
    {
        $this->adventure = [

            'title' => 'AI Explorer',

            'description' =>
                'Build your first AI creation',

            'icon' => '🤖',

            'xp' => 50,

        ];
    }


    public function startAdventure()
    {
        // Future:
        // create learning journey
        // award XP
        // open lesson

    }


    public function render()
    {
        return view(
            'livewire.child.explore.components.featured-adventure'
        );
    }
}