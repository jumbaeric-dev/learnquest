<?php

namespace App\View\Components\Child\Worlds;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WorldHero extends Component
{
    public function __construct(
        public array $hero,
    ) {}

    public function render(): View|Closure|string
    {
        return view(
            'components.child.worlds.world-hero'
        );
    }
}