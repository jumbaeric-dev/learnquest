<?php

namespace App\Livewire\Child\Missions;

use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\MissionDiscoveryService;
use Livewire\Component;

class Index extends Component
{
    public string $selectedCategory = 'all';

    protected $listeners = [
        'mission-category-selected' => 'changeCategory',
    ];

    public function mount(CurrentChildService $currentChild): void
    {
        if (! $currentChild->current()) {
            abort(403);
        }
    }

    public function render(
        CurrentChildService $currentChild,
        MissionDiscoveryService $discovery,
    ) {
        $child = $currentChild->current();

        abort_unless($child, 403);

        return view('livewire.child.missions.index', [
            'missions' => $discovery->getMissions(
                $child,
                $this->selectedCategory,
            ),
            'categories' => $discovery->getCategories(),
        ]);
    }

    public function selectCategory(string $category): void
    {
        $this->selectedCategory = $category;
    }

    public function changeCategory(string $category): void
    {
        $this->selectedCategory = $category;
    }
}
