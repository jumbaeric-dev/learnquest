<?php

namespace App\Livewire\Child\Worlds;

use App\Models\Subject;
use App\Services\Child\ChildDashboardLoader;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Worlds\WorldService;
use Livewire\Component;

class Show extends Component
{
    public array $world = [];

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

        $child = $loader->load($child);

        $this->world = $worldService
            ->world($child, $subject)
            ->toArray();
    }

    public function render()
    {
        return view('livewire.child.worlds.show');
    }
}