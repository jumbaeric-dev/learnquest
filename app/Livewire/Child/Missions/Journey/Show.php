<?php

namespace App\Livewire\Child\Missions\Journey;


use Livewire\Component;
use App\Services\Child\Learning\MissionJourneyService;


class Show extends Component
{


    public array $mission;


    public function mount(
        string $mission,
        MissionJourneyService $service
    )
    {

        $this->mission =
            $service->getMission($mission);

    }



    public function render()
    {

        return view(
            'livewire.child.missions.journey.show'
        );

    }


}