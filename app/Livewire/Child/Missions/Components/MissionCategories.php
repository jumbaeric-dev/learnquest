<?php

namespace App\Livewire\Child\Missions\Components;

use Livewire\Component;

class MissionCategories extends Component
{
    public array $categories = [];
    public string $selectedCategory = 'all';

    public function mount()
    {
        $this->categories = [
            [
                'name' => 'all',
                'label' => 'All',
                'icon' => '🌎',
            ],
            [
                'name' => 'Science',
                'label' => 'Science',
                'icon' => '🔬',
            ],

            [
                'name' => 'Technology',
                'label' => 'Technology',
                'icon' => '💻',
            ],

            [
                'name' => 'Creativity',
                'label' => 'Creativity',
                'icon' => '🎨',
            ],

            [
                'name' => 'AI',
                'label' => 'AI',
                'icon' => '🤖',
            ],

            [
                'name' => 'Life Skills',
                'label' => 'Life Skills',
                'icon' => '🌱',
            ],

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
