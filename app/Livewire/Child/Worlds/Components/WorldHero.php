<?php

namespace App\Livewire\Child\Worlds\Components;

use Livewire\Component;

class WorldHero extends Component
{
    public array $world = [];

    public function render()
    {
        return view(
            'livewire.child.worlds.components.world-hero'
        );
    }
}