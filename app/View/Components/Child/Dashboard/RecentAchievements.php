<?php

namespace App\View\Components\Child\Dashboard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RecentAchievements extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $achievements,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.child.dashboard.recent-achievements');
    }
}
