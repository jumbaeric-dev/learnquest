<?php

namespace App\View\Components\Child\Dashboard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SkillsPreview extends Component
{
    public function __construct(
        public array $skills,
    ) {}

    public function render(): View|Closure|string
    {
        return view(
            'components.child.dashboard.skills-preview'
        );
    }
}