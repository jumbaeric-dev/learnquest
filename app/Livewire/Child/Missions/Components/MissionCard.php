<?php

namespace App\Livewire\Child\Missions\Components;

use Livewire\Component;

class MissionCard extends Component
{

    public array $mission;


    public function startMission()
    {
        return $this->redirect(
            route(
                'child.mission.show',
                'math-galaxy'
            )
        );

        // Future:
        // redirect to mission journey
    }



    public function render()
    {
        return view(
            'livewire.child.missions.components.mission-card'
        );
    }
}
