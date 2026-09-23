<?php

namespace App\Livewire\Child\Missions\Components;

use App\Models\Course;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Progress\CourseProgressService;
use Livewire\Component;

class MissionCard extends Component
{
    public array $mission;

    public function startMission(
        CurrentChildService $currentChild,
        CourseProgressService $courseProgress,
    ) {
        $child = $currentChild->current();

        $course = Course::findOrFail($this->mission['id']);

        $courseProgress->update($child, $course);

        return $this->redirectRoute('learn.course', $course);
    }

    public function render()
    {
        return view(
            'livewire.child.missions.components.mission-card'
        );
    }
}
