<?php

namespace App\View\Components\Child\Worlds;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WorldProgress extends Component
{
    public function __construct(
        public array $progress,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.child.worlds.world-progress');
    }
}