<?php

namespace App\View\Components\Child\Dashboard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LearningWorlds extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $worlds,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.child.dashboard.learning-worlds');
    }
}
