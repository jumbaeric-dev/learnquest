<?php

namespace App\Livewire\Child\Missions\Components;

use App\Services\Child\Learning\MissionDiscoveryService;
use Livewire\Component;

class MissionCategories extends Component
{
    public array $categories = [];
    public string $selectedCategory = 'all';

    public function mount(MissionDiscoveryService $missions)
    {
        $this->categories = [
            [
                'name' => 'all',
                'label' => 'All',
                'icon' => '🌎',
            ],

            ...collect($missions->getCategories())
                ->map(fn($category) => [
                    'name' => $category['name'],
                    'label' => $category['name'],
                    'icon' => $category['icon'],
                ])
                ->all(),
        ];
    }

    public function selectCategory(string $category)
    {
        $this->selectedCategory = $category;

        $this->dispatch(
            'mission-category-selected',
            category: $category
        );
    }

    public function render()
    {
        return view(
            'livewire.child.missions.components.mission-categories'
        );
    }
}
