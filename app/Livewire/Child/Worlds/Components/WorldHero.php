<?php

namespace App\Livewire\Child\Worlds\Components;

use Livewire\Component;

class WorldHero extends Component
{
    public array $world = [];

    public function mount(array $world): void
    {
        $this->world = $world;
    }

    public function continueAdventure(): void
    {
        // We will connect this to the child's learning journey later.
    }

    public function render()
    {
        return view(
            'livewire.child.worlds.components.world-hero'
        );
    }
}