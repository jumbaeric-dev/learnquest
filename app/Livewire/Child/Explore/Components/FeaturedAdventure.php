<?php

namespace App\Livewire\Child\Explore\Components;

use App\Models\Course;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Dashboard\UniverseDashboardService;
use App\Services\Child\Learning\LearningContentAccessService;
use App\Services\Progress\CourseProgressService;
use Livewire\Component;

class FeaturedAdventure extends Component
{
    public ?array $adventure = null;

    public function mount(
        CurrentChildService $currentChild,
        UniverseDashboardService $dashboardService,
    ): void {
        $child = $currentChild->current();

        if (! $child) {
            return;
        }

        $recommended = $dashboardService->recommendedAdventures($child);

        $featured = $recommended[0] ?? null;

        if (! $featured) {
            return;
        }

        $course = Course::find($featured->id);

        $this->adventure = [
            'id' => $featured->id,
            'title' => $featured->title,
            'description' => $featured->description,
            'icon' => $featured->icon ?? '🚀',
            'xp' => $this->totalXp($course),
        ];
    }

    public function startAdventure(
        CurrentChildService $currentChild,
        CourseProgressService $courseProgress,
        LearningContentAccessService $access,
    ) {
        if (! $this->adventure) {
            return;
        }

        $child = $currentChild->current();

        abort_unless($child, 403);

        $course = Course::findOrFail($this->adventure['id']);

        $course = $access->course($course);

        $courseProgress->update($child, $course);

        return $this->redirectRoute('learn.course', $course);
    }

    protected function totalXp(?Course $course): int
    {
        if (! $course) {
            return 0;
        }

        return (int) $course->modules
            ->flatMap(fn($module) => $module->lessons)
            ->flatMap(fn($lesson) => $lesson->activities)
            ->sum('xp_reward');
    }

    public function render()
    {
        return view('livewire.child.explore.components.featured-adventure');
    }
}
