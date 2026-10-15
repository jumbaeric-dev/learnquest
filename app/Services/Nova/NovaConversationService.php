<?php

namespace App\Services\Nova;

use App\Contracts\Ai\NovaAiProviderContract;
use App\Exceptions\NovaAiException;
use App\Models\Child;
use App\Models\NovaMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class NovaConversationService
{
    private const FALLBACK_MESSAGE =
        'My circuits are a bit fuzzy right now 🤖💫 '
        .'Can you try asking me again in a moment?';

    public function __construct(
        protected NovaAiProviderContract $provider,
        protected NovaProviderFactory $providerFactory,
    ) {
    }

    /**
     * The most recent messages for this child, oldest first.
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
            ->map(fn (NovaMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->all();
    }

    /**
     * Whether this child has hit their daily Nova message limit.
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
     * Try the configured primary provider, followed by fallbacks.
     *
     * Persist the child's message once and the final assistant reply
     * once. Record the provider that successfully generated the reply.
     */
    public function respond(Child $child, string $message): string
    {
        $history = $this->history($child);

        $child->novaMessages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        $reply = self::FALLBACK_MESSAGE;
        $successfulProvider = null;

        foreach ($this->providersToTry() as $provider) {
            try {
                $reply = $provider->reply($child, $message, $history);
                $successfulProvider = $provider->name();

                break;
            } catch (Throwable $e) {
                Log::warning('Nova provider attempt failed', [
                    'child_id' => $child->id,
                    'provider' => $provider->name(),
                    'exception' => $e::class,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($successfulProvider === null) {
            Log::warning('Nova could not get a reply from any configured provider', [
                'child_id' => $child->id,
            ]);
        }

        $child->novaMessages()->create([
            'role' => 'assistant',
            'content' => $reply,
            'provider' => $successfulProvider ?? $this->provider->name(),
        ]);

        return $reply;
    }

    /**
     * Resolve providers in order, skipping duplicate provider names.
     *
     * @return array<int, NovaAiProviderContract>
     */
    protected function providersToTry(): array
    {
        $providers = [$this->provider];
        $seen = [$this->provider->name() => true];

        $fallbacks = config('learnquest.nova.fallback_providers', []);

        if (! is_array($fallbacks)) {
            return $providers;
        }

        foreach ($fallbacks as $name) {
            if (! is_string($name) || trim($name) === '') {
                continue;
            }

            try {
                $fallback = $this->providerFactory->make(trim($name));
            } catch (Throwable $e) {
                Log::warning('Nova fallback provider could not be initialized', [
                    'provider' => $name,
                    'exception' => $e::class,
                ]);

                continue;
            }

            $canonicalName = $fallback->name();

            if (isset($seen[$canonicalName])) {
                continue;
            }

            $seen[$canonicalName] = true;
            $providers[] = $fallback;
        }

        return $providers;
    }
}