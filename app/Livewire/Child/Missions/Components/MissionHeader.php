<?php

namespace App\Livewire\Child\Missions\Components;

use Livewire\Component;

class MissionHeader extends Component
{

    public string $childName = 'Explorer';
    public int $level = 5;
    public int $xp = 340;
    public int $streak = 3;

    public function render()
    {
        return view(
            'livewire.child.missions.components.mission-header'
        );
    }

}