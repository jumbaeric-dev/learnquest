<?php

namespace App\Livewire\Child\Worlds;

use App\Models\Subject;
use App\Services\Child\ChildDashboardLoader;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\LearningJourneyService;
use App\Services\Child\Worlds\WorldService;
use Livewire\Component;

class Show extends Component
{
    public array $world = [];

    public Subject $subject;

    public function mount(
        Subject $subject,
        CurrentChildService $currentChild,
        ChildDashboardLoader $loader,
        WorldService $worldService,
    ): void {
        $child = $currentChild->current();

        if (! $child) {
            abort(403);
        }

        $this->subject = $subject;

        $child = $loader->load($child);

        $this->world = $worldService
            ->world($child, $subject)
            ->toArray();
    }

    /**
     * "Continue Journey" CTA on the world hero — resumes the
     * child's current lesson if this world's course is active.
     */
    public function continueJourney(
        CurrentChildService $currentChild,
        LearningJourneyService $journey,
    ) {
        $child = $currentChild->current();

        $lesson = $journey->getCurrentLesson($child);

        if (! $lesson) {
            return;
        }

        return $this->redirectRoute('learn.lesson', $lesson);
    }

    public function render()
    {
        return view('livewire.child.worlds.show');
    }
}