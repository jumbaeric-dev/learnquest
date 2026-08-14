<?php

namespace App\View\Components\Lq;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WorldTile extends Component
{
    public function __construct(
        public string $title,
        public string $description,
        public string $icon,
        public int $progress = 0,
        public int $xp = 0,
        public string $theme = 'blue',
        public bool $locked = false,
        public ?string $badge = null,
        public string $actionLabel = 'Explore',
        public bool $showAction = true,
    ) {}

    public function gradient(): string
    {
        return match ($this->theme) {
            'purple' => 'from-violet-500 to-fuchsia-500',
            'green'  => 'from-emerald-500 to-green-500',
            'orange' => 'from-orange-500 to-amber-500',
            'pink'   => 'from-pink-500 to-rose-500',
            'amber'  => 'from-amber-400 to-orange-500',
            'emerald' => 'from-emerald-400 to-teal-500',
            default  => 'from-sky-500 to-cyan-500',
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.lq.world-tile');
    }
}
