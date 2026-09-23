<?php

namespace App\Services\Nova;

use App\Contracts\Ai\NovaAiProviderContract;
use App\Exceptions\NovaAiException;
use App\Models\Child;
use App\Models\NovaMessage;
use Illuminate\Support\Facades\Log;

class NovaConversationService
{
    public function __construct(
        protected NovaAiProviderContract $provider,
    ) {
    }

    /**
     * The most recent messages for this child, oldest first,
     * shaped for display in the chat widget.
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function history(Child $child): array
    {
        $limit = config('learnquest.nova.history_limit', 20);

        return $child->novaMessages()
            ->latest('id')
            ->take($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn(NovaMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->all();
    }

    /**
     * Whether this child has hit their daily Nova message limit.
     *
     * A light safety/cost guard — not meant to be a hard security
     * boundary, just a sane default so a single child account
     * can't run up unbounded API usage in one day.
     */
    public function hasReachedDailyLimit(Child $child): bool
    {
        $limit = config('learnquest.nova.daily_message_limit', 60);

        $sentToday = $child->novaMessages()
            ->where('role', 'user')
            ->whereDate('created_at', today())
            ->count();

        return $sentToday >= $limit;
    }

    /**
     * Send the child's message to Nova and return the reply,
     * persisting both sides of the exchange.
     *
     * Never throws — if the AI provider fails for any reason,
     * a friendly in-character fallback message is returned and
     * persisted instead, so the chat UI never breaks.
     */
    public function respond(Child $child, string $message): string
    {
        $history = $this->history($child);

        $child->novaMessages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        try {
            $reply = $this->provider->reply($child, $message, $history);
        } catch (NovaAiException $e) {
            Log::warning('Nova fell back after provider failure', [
                'child_id' => $child->id,
                'provider' => $this->provider->name(),
                'error' => $e->getMessage(),
            ]);

            $reply = "My circuits are a bit fuzzy right now 🤖💫 "
                ."Can you try asking me again in a moment?";
        }

        $child->novaMessages()->create([
            'role' => 'assistant',
            'content' => $reply,
            'provider' => $this->provider->name(),
        ]);

        return $reply;
    }
}
