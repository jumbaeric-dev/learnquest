<?php

namespace App\View\Components\Child\Dashboard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WelcomeSection extends Component
{
    public function __construct(
        public array $welcome,
    ) {}

    public function render(): View|Closure|string
    {
        return view(
            'components.child.dashboard.welcome-section'
        );
    }
}