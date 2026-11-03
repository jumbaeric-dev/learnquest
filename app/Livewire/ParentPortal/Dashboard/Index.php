<?php

namespace App\Livewire\ParentPortal\Dashboard;

use App\Services\ParentPortal\ParentDashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        return view("livewire.parent-portal.dashboard.index", [
            "overview" => app(ParentDashboardService::class)->overview(
                auth()->user()
            ),
        ]);
    }
}