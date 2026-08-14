<?php

namespace App\View\Components\Lq;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Avatar extends Component
{
    public function __construct(
        public ?string $image = null,
        public string $size = 'md',
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.lq.avatar');
    }
}