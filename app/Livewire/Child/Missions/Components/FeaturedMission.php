<?php

namespace App\Livewire\Child\Missions\Components;

use App\Models\Course;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Dashboard\UniverseDashboardService;
use App\Services\Child\Learning\MissionDiscoveryService;
use App\Services\Progress\CourseProgressService;
use Livewire\Component;

class FeaturedMission extends Component
{
    public ?array $mission = null;

    public function mount(
        CurrentChildService $currentChild,
        UniverseDashboardService $dashboardService,
        MissionDiscoveryService $missions,
    ): void {
        $child = $currentChild->current();

        if (! $child) {
            return;
        }

        $recommended = $dashboardService->recommendedAdventures($child);

        $courseId = $recommended[0]->id ?? null;

        if (! $courseId) {
            return;
        }

        $course = Course::with(['subject', 'modules.lessons.activities'])
            ->find($courseId);

        if (! $course) {
            return;
        }

        $this->mission = collect($missions->getMissions('all'))
            ->firstWhere('id', $course->id);
    }

    public function startMission(
        CurrentChildService $currentChild,
        CourseProgressService $courseProgress,
    ) {
        if (! $this->mission) {
            return;
        }

        $child = $currentChild->current();

        $course = Course::findOrFail($this->mission['id']);

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
