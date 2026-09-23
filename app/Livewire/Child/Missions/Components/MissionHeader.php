<?php

namespace App\Livewire\Child\Missions\Components;

use App\Services\Child\Context\CurrentChildService;
use App\Services\Progress\StreakService;
use Livewire\Component;

class MissionHeader extends Component
{
    public string $childName = 'Explorer';
    public int $level = 1;
    public int $xp = 0;
    public int $streak = 0;

    public function mount(
        CurrentChildService $currentChild,
        StreakService $streaks,
    ): void {
        $child = $currentChild->current();

        if (! $child) {
            return;
        }

        $this->childName = $child->first_name;
        $this->level = $child->level;
        $this->xp = $child->xp;
        $this->streak = $streaks->current($child);
    }

    public function render()
    {
        return view(
            'livewire.child.missions.components.mission-header'
        );
    }
}
