<?php

namespace App\View\Components\Lq;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GlassCard extends Component
{
    public function __construct(
        public string $padding = 'md',
        public string $radius = 'xl',
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.lq.glass-card');
    }
}