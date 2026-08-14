<?php

namespace App\View\Components\Lq;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProgressBar extends Component
{
    public float $percentage;

    public function __construct(
        public int|float $value,
        public int|float $max,
        public string $color = 'indigo',
        public bool $showLabel = true,
    ) {
        $this->percentage = $max > 0
            ? min(100, ($value / $max) * 100)
            : 0;
    }

    public function render(): View|Closure|string
    {
        return view('components.lq.progress-bar');
    }
}