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
        match ($action) {
            'nova'
            => redirect()
                ->route('nova'),
            'mission'
            => $this->dispatch(
                'scroll-to-mission'
            ),
            'create'
            => redirect('/creator'),
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.child.layouts.components.floating-actions');
    }
}
