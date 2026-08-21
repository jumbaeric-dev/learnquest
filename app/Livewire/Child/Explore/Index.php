<?php

namespace App\Livewire\Child\Explore;

use Livewire\Component;
use App\Services\Child\Context\CurrentChildService;

class Index extends Component
{
    public function mount(CurrentChildService $currentChild): void
    {
        if (! $currentChild->current()) {
            abort(403);
        }
    }
  
    public function render()
    {
        return view('livewire.child.explore.index');
    }
}