<?php

namespace App\Services\Nova\Providers;

use App\Exceptions\NovaAiException;
use App\Models\Child;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Alternative Nova engine, powered by OpenAI's chat completions API.
 *
 * Selected instead of Claude by setting NOVA_AI_PROVIDER=openai
 * in .env — no other code changes are required. This exists to
 * prove out (and keep exercising) LearnQuest's provider-agnostic
 * design; additional vendors follow the same pattern.
 */
class OpenAiNovaProvider extends AbstractNovaProvider
{
    public function name(): string
    {
        return 'openai';
    }

    public function reply(Child $child, string $message, array $history): string
    {
        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
            throw new NovaAiException(
                'OpenAI API key is not configured (OPENAI_API_KEY).'
            );
        }

        $messages = $this->toOpenAiMessages($child, $history, $message);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'max_tokens' => config('learnquest.nova.max_tokens', 300),
                    'messages' => $messages,
                ]);
        } catch (Throwable $e) {
            Log::warning('Nova (OpenAI) request failed', ['error' => $e->getMessage()]);

            throw new NovaAiException('Could not reach OpenAI.', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Nova (OpenAI) returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new NovaAiException(
                "OpenAI API responded with status {$response->status()}."
            );
        }

        $text = $response->json('choices.0.message.content');

        if (! $text) {
            throw new NovaAiException('OpenAI response contained no message content.');
        }

        return trim($text);
    }

    /**
     * OpenAI's chat completions format puts the system prompt as
     * the first message in the array, rather than a separate field.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<int, array{role: string, content: string}>
     */
    protected function toOpenAiMessages(Child $child, array $history, string $message): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->buildSystemPrompt($child),
            ],
        ];

        foreach ($history as $turn) {
            $messages[] = [
                'role' => $turn['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $turn['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        return $messages;
    }
}
