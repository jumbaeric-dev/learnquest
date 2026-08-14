<?php

namespace App\Livewire\Child\Dashboard;

use Livewire\Component;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\ChildDashboardLoader;
use App\Services\Child\Dashboard\UniverseDashboardService;

class Index extends Component
{
    public array $dashboard = [];

    public function mount(
        CurrentChildService $currentChild,
        ChildDashboardLoader $loader,
        UniverseDashboardService $dashboardService,
    ): void {

        $child = $currentChild->current();

        if (! $child) {
            abort(403);
        }

        $child = $loader->load($child);

        $this->dashboard = $dashboardService
            ->dashboard($child)
            ->toArray();
    }

     public function render()
    {
        return view(
            'livewire.child.dashboard.index'
        );
    }
}