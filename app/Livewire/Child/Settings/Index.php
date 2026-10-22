<?php

namespace App\Livewire\Child\Settings;

use App\Services\Child\Context\CurrentChildService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public bool $soundEffects = true;

    public bool $reducedAnimations = false;

    public function mount(CurrentChildService $currentChild): void
    {
        $child = $currentChild->current();

        abort_unless($child, 403);

        $settings = $child->settings ?? [];

        $this->soundEffects = (bool) ($settings['sound_effects'] ?? true);
        $this->reducedAnimations = (bool) ($settings['reduced_animations'] ?? false);
    }

    public function save(CurrentChildService $currentChild): void
    {
        $this->validate([
            'soundEffects' => ['required', 'boolean'],
            'reducedAnimations' => ['required', 'boolean'],
        ]);

        $child = $currentChild->current();

        abort_unless($child, 403);

        $settings = $child->settings ?? [];

        $settings['sound_effects'] = $this->soundEffects;
        $settings['reduced_animations'] = $this->reducedAnimations;

        $child->settings = $settings;
        $child->save();

        session()->flash('settings_saved', 'Your settings have been saved!');
    }

    public function render(): View
    {
        return view('livewire.child.settings.index');
    }
}
