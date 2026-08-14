<?php

namespace App\View\Components\Child\Dashboard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ExplorerHeader extends Component
{
    public function __construct(
        public array $explorer,
    ) {}

    public function render(): View|Closure|string
    {
        return view(
            'components.child.dashboard.explorer-header'
        );
    }
}