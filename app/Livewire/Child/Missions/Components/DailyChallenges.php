<?php

namespace App\Livewire\Child\Missions\Components;

use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\DailyChallengeService;
use Livewire\Attributes\On;
use Livewire\Component;

class DailyChallenges extends Component
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public array $challenges = [];

    public function mount(): void
    {
        $this->refreshChallenges();
    }

    /**
     * Re-read today's state. Also runs when the child sends Nova a
     * message, so "Ask Nova AI" flips to claimable immediately.
     */
    #[On('nova-message-sent')]
    public function refreshChallenges(): void
    {
        $child = app(CurrentChildService::class)->current();

        $this->challenges = $child
            ? app(DailyChallengeService::class)->forChild($child)
            : [];
    }

    public function claim(
        string $key,
        CurrentChildService $currentChild,
        DailyChallengeService $service
    ) {
        $child = $currentChild->current();

        abort_unless($child, 403);

        if ($currentChild->isParentPreview()) {
            session()->flash(
                'message',
                __('localization.preview_challenge_notice')
            );

            return $this->redirectRoute('child.missions');
        }

        $result = $service->claim($child, $key);

        if (! $result) {
            // Not completed, unknown, or already claimed — just resync.
            $this->refreshChallenges();

            return;
        }

        $message = __('localization.xp_earned_notice', ['xp' => $result['awarded']]);

        if ($result['leveled_up']) {
            $message .= ' '.__('localization.level_up_notice');
        }

        session()->flash('message', $message);

        // Reload so the topbar and header XP/level update too.
        return $this->redirectRoute('child.missions');
    }

    public function render()
    {
        return view('livewire.child.missions.components.daily-challenges');
    }
}
