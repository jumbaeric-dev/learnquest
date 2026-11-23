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
                'title' => __('localization.my_universe'),
                'icon' => '🌌',
                'route' => '/child/my-universe',
            ],

            [
                'title' => __('localization.explorer'),
                'icon' => '🗺',
                'route' => '/child/explore',
            ],

            [
                'title' => __('localization.missions'),
                'icon' => '🎯',
                'route' => '/child/missions',
            ],

            [
                'title' => __('localization.backpack'),
                'icon' => '🎒',
                'route' => '#',
            ],

            [
                'title' => __('localization.my_profile'),
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