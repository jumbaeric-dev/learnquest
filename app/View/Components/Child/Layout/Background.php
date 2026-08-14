<?php

namespace App\View\Components\Child\Layout;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Background extends Component
{
    public function __construct(
        public string $theme = 'sky',
    ) {}

    public function gradient(): string
    {
        return match ($this->theme) {

            'space' => 'from-slate-950 via-indigo-950 to-violet-950',

            'forest' => 'from-green-100 via-emerald-50 to-lime-100',

            'science' => 'from-cyan-100 via-blue-50 to-sky-100',

            'coding' => 'from-orange-100 via-amber-50 to-yellow-100',

            'ai' => 'from-fuchsia-100 via-pink-50 to-violet-100',

            'creative' => 'from-amber-100 via-orange-50 to-pink-100',

            'life' => 'from-emerald-100 via-green-50 to-teal-100',

            default => 'from-sky-100 via-cyan-50 to-indigo-100',
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.child.layout.background');
    }
}
