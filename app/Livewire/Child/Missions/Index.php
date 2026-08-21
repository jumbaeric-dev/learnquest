<?php

namespace App\Livewire\Child\Missions;

use Livewire\Component;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\MissionDiscoveryService;

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

    public function render()
    {
        return view('livewire.child.missions.index', [
            'missions' => app(MissionDiscoveryService::class)
                ->getMissions($this->selectedCategory),

            'categories' => app(MissionDiscoveryService::class)
                ->getCategories(),
        ]);
    }


    public function selectCategory(string $category)
    {
        $this->selectedCategory = $category;
    }

    public function changeCategory($category)
    {
        $this->selectedCategory = $category;
    }
}
