<?php

namespace App\View\Components\Lq;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Badge extends Component
{
    public function __construct(
        public string $variant = 'common',
        public ?string $icon = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.lq.badge');
    }
}