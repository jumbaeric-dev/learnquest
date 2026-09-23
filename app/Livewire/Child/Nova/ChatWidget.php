<?php

namespace App\Livewire\Child\Nova;

use App\Services\Child\Context\CurrentChildService;
use App\Services\Nova\NovaConversationService;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatWidget extends Component
{
    public bool $open = false;

    public string $draft = '';

    public bool $sending = false;

    public bool $limitReached = false;

    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    public function mount(
        CurrentChildService $currentChild,
        NovaConversationService $nova,
    ): void {
        $child = $currentChild->current();

        if (! $child) {
            return;
        }

        $this->messages = $nova->history($child);

        $this->limitReached = $nova->hasReachedDailyLimit($child);
    }

    #[On('open-nova-chat')]
    public function open(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function send(
        CurrentChildService $currentChild,
        NovaConversationService $nova,
    ) {
        $message = trim($this->draft);

        if ($message === '' || $this->sending || $this->limitReached) {
            return;
        }

        $child = $currentChild->current();

        if (! $child) {
            return;
        }

        $this->messages[] = ['role' => 'user', 'content' => $message];

        $this->draft = '';

        $this->sending = true;

        $reply = $nova->respond($child, $message);

        $this->messages[] = ['role' => 'assistant', 'content' => $reply];

        $this->sending = false;

        $this->limitReached = $nova->hasReachedDailyLimit($child);

        // Lets the Missions page's "Ask Nova AI" daily challenge
        // complete itself when a real conversation happens.
        $this->dispatch('nova-message-sent');
    }

    public function render()
    {
        return view('livewire.child.nova.chat-widget');
    }
}
