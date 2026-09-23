<?php

namespace App\Livewire\Child\Layouts\Components;

use Livewire\Component;

class FloatingActions extends Component
{
    public array $actions = [];

    public function mount(): void
    {
        $this->actions = [
            [
                'title' => 'Ask Nova',
                'icon' => '🤖',
                'action' => 'nova',
            ],

            [
                'title' => 'Mission',
                'icon' => '🚀',
                'action' => 'mission',
            ],

            [
                'title' => 'Create',
                'icon' => '🎨',
                'action' => 'create',
            ],
        ];
    }

    public function execute(string $action)
    {
        return match ($action) {

            // Opens the always-available chat widget in place,
            // rather than navigating away to a separate page.
            'nova' => $this->dispatch('open-nova-chat'),

            // The dashboard is the one place a "current mission"
            // section reliably exists, so route there and land on
            // it directly rather than assuming the current page
            // has a matching element to scroll to.
            'mission' => $this->redirect(
                route('child.dashboard').'#current-mission'
            ),

            // The Creator tool doesn't exist yet in V1 — flash a
            // friendly notice instead of hitting a dead route.
            'create' => $this->createComingSoon(),

            default => null,
        };
    }

    protected function createComingSoon()
    {
        session()->flash(
            'message',
            '🎨 The Creator tool is coming soon!'
        );
    }

    public function render()
    {
        return view('livewire.child.layouts.components.floating-actions');
    }
}
