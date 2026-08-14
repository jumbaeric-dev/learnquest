<?php

namespace App\Livewire\Child\Layouts\Components;

use Livewire\Component;

class BottomNavigation extends Component
{
    public array $items = [];

    public string $activeRoute = '';

    public function mount(): void
    {
        $this->activeRoute = request()->path();

        $this->items = [

            [
                'title' => 'Universe',
                'icon' => '🌌',
                'route' => '/child/my-universe',
            ],

            [
                'title' => 'Explore',
                'icon' => '🗺',
                'route' => '/child/explore',
            ],

            [
                'title' => 'Missions',
                'icon' => '🎯',
                'route' => '/child/missions',
            ],

            [
                'title' => 'Backpack',
                'icon' => '🎒',
                'route' => '#',
            ],

            [
                'title' => 'Profile',
                'icon' => '⭐',
                'route' => '#',
            ],

        ];
    }


    public function render()
    {
        return view(
            'livewire.child.layouts.components.bottom-navigation'
        );
    }
}