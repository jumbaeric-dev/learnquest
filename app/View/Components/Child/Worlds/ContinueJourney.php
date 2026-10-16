<?php

namespace App\View\Components\Child\Worlds;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ContinueJourney extends Component
{
    /**
     * Create a new component instance.
     *
     * @param array{
     *     course: string,
     *     lesson: string,
     *     progress: int,
     *     buttonText: string,
     *     buttonAction: string
     * } $journey
     */
    public function __construct(
        public array $journey,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.child.worlds.continue-journey');
    }
}
