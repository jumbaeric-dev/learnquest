<?php

namespace App\View\Components\Lq;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class IconButton extends Component
{
    public function __construct(
        public ?string $badge = null,
        public string $size = 'md',
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.lq.icon-button');
    }
}