<?php

namespace App\View\Components\Child\Dashboard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ContinueLearning extends Component
{
    public function __construct(
        public ?array $learning,
    ) {}

    public function render(): View|Closure|string
    {
        return view(
            'components.child.dashboard.continue-learning'
        );
    }
}