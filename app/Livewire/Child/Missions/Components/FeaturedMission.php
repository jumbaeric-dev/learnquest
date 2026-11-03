<?php

namespace App\Livewire\Child\Missions\Components;

use App\Models\Course;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\LearningContentAccessService;
use App\Services\Child\Learning\MissionDiscoveryService;
use App\Services\Progress\CourseProgressService;
use Livewire\Component;

class FeaturedMission extends Component
{
    public ?array $mission = null;

    public function mount(
        CurrentChildService $currentChild,
        MissionDiscoveryService $missions,
    ): void {
        $child = $currentChild->current();

        if (! $child) {
            return;
        }

        // MissionDiscoveryService already enforces publication,
        // active-world, and child age-band requirements.
        $this->mission = collect($missions->getMissions($child, 'all'))
            ->first();
    }

    public function startMission(
        CurrentChildService $currentChild,
        CourseProgressService $courseProgress,
        LearningContentAccessService $access,
    ) {
        if (! $this->mission) {
            return;
        }

        $child = $currentChild->current();

        abort_unless($child, 403);

        $course = Course::findOrFail($this->mission['id']);

        // Recheck authorization when the child starts the mission.
        $course = $access->course($course, $child);

        $courseProgress->update($child, $course);

        return $this->redirectRoute('learn.course', $course);
    }

    public function render()
    {
        return view(
            'livewire.child.missions.components.featured-mission'
        );
    }
}
