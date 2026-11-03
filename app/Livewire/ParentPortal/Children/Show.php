<?php

namespace App\Livewire\ParentPortal\Children;

use App\Models\User;
use App\Services\ParentPortal\ParentDashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public int $childId;

    public function mount(int $child): void
    {
        $this->childId = $child;
        $this->ownedChild();
    }

    public function render(ParentDashboardService $dashboard): View
    {
        $parent = auth()->user();
        abort_unless($parent instanceof User && $parent->isParent(), 403);

        return view('livewire.parent-portal.children.show', [
            'report' => $dashboard->childDetails($parent, $this->childId),
            'canSeeSkillReports' => app(\App\Services\Billing\EntitlementService::class)
                ->allows($parent, 'skill_reports'),
        ]);
    }

    protected function ownedChild()
    {
        $parent = auth()->user();
        abort_unless($parent instanceof User && $parent->isParent(), 403);

        return $parent->children()->findOrFail($this->childId);
    }
}
