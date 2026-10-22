<?php

namespace App\Livewire\Child\Profile;

use App\Services\Child\Context\CurrentChildService;
use Livewire\Component;

class Show extends Component
{
    public array $profile = [];

    public function mount(CurrentChildService $currentChild): void
    {
        $child = $currentChild->current();

        if (! $child) {
            abort(403);
        }

        $this->profile = [
            'name' => $child->full_name ?: $child->first_name,
            'username' => $child->username,
            'avatar' => $child->avatar,
            'age' => $child->date_of_birth?->age,
            'level' => (int) $child->level,
            'xp' => (int) $child->xp,
            'completed_activities' => $child
                ->activityProgress()
                ->where('completed', true)
                ->count(),
            'streak' => (int) ($child->streak?->current_streak ?? 0),
            'badges_count' => $child->badges()->count(),
            'skills_count' => $child->skillProgress()->count(),
        ];
    }

    public function render()
    {
        return view('livewire.child.profile.show');
    }
}
