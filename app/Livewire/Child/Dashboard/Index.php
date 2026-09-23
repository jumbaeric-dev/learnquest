<?php

namespace App\Livewire\Child\Dashboard;

use App\Models\Course;
use App\Services\Child\ChildDashboardLoader;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Dashboard\UniverseDashboardService;
use App\Services\Child\Learning\LearningContentAccessService;
use App\Services\Child\Learning\LearningJourneyService;
use App\Services\Progress\CourseProgressService;
use Livewire\Component;

class Index extends Component
{
    public array $dashboard = [];

    public function mount(
        CurrentChildService $currentChild,
        ChildDashboardLoader $loader,
        UniverseDashboardService $dashboardService,
    ): void {

        $child = $currentChild->current();

        if (! $child) {
            abort(403);
        }

        $child = $loader->load($child);

        $this->dashboard = $dashboardService
            ->dashboard($child)
            ->toArray();
    }

    /**
     * Welcome card CTA.
     *
     * Sends the child into their current mission if they have
     * one, otherwise into Explore to pick their first adventure.
     */
    public function welcomeAction(
        CurrentChildService $currentChild,
        LearningJourneyService $journey
    ) {
        $child = $currentChild->current();

        $activity = $journey->getCurrentActivity($child);

        if ($activity) {
            return $this->redirectRoute('learn.activity', $activity);
        }

        return $this->redirectRoute('child.explore');
    }

    /**
     * "Current Mission" / "Daily Challenge" CTA — jump straight
     * into the next activity in the child's active journey.
     */
    public function startMission(
        CurrentChildService $currentChild,
        LearningJourneyService $journey
    ) {
        $child = $currentChild->current();

        $activity = $journey->getCurrentActivity($child);

        if (! $activity) {
            return $this->redirectRoute('child.explore');
        }

        return $this->redirectRoute('learn.activity', $activity);
    }

    /**
     * "Continue Learning" CTA — resume the current lesson.
     */
    public function continueLesson(
        CurrentChildService $currentChild,
        LearningJourneyService $journey
    ) {
        $child = $currentChild->current();

        $lesson = $journey->getCurrentLesson($child);

        if (! $lesson) {
            return $this->redirectRoute('child.explore');
        }

        return $this->redirectRoute('learn.lesson', $lesson);
    }

    /**
     * "Explore" CTA on a recommended adventure.
     *
     * Starting a brand new course requires a CourseProgress row
     * to exist before LearningContentAccessService will allow the
     * child to view it, so one is created here if it's missing.
     */
    public function explore(
        int $courseId,
        CurrentChildService $currentChild,
        CourseProgressService $courseProgress,
        LearningContentAccessService $access,
    ) {
        $child = $currentChild->current();

        abort_unless($child, 403);

        $course = Course::findOrFail($courseId);

        $course = $access->course($course);

        $courseProgress->update($child, $course);

        return $this->redirectRoute('learn.course', $course);
    }

    /**
     * Nova card CTA — opens the persistent chat widget.
     */
    public function openNova(): void
    {
        $this->dispatch('open-nova-chat');
    }

    public function render()
    {
        return view(
            'livewire.child.dashboard.index'
        );
    }
}
