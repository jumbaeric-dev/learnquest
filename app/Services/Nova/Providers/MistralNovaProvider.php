<?php

namespace App\Services\Nova\Providers;

use App\Exceptions\NovaAiException;
use App\Models\Child;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MistralNovaProvider extends AbstractNovaProvider
{
    public function name(): string
    {
        return 'mistral';
    }

    public function reply(Child $child, string $message, array $history): string
    {
        $apiKey = config('services.mistral.api_key');

        if (! $apiKey) {
            throw new NovaAiException(
                'Mistral API key is not configured (MISTRAL_API_KEY).'
            );
        }

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

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post('https://api.mistral.ai/v1/chat/completions', [
                    'model' => config('services.mistral.model', 'mistral-small-latest'),
                    'max_tokens' => config('learnquest.nova.max_tokens', 300),
                    'messages' => $messages,
                ]);
        } catch (Throwable $e) {
            Log::warning('Nova (Mistral) request failed', [
                'error' => $e->getMessage(),
            ]);

            throw new NovaAiException('Could not reach Mistral.', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Nova (Mistral) returned an error', [
                'status' => $response->status(),
            ]);

            throw new NovaAiException(
                "Mistral API responded with status {$response->status()}."
            );
        }

        $text = $response->json('choices.0.message.content');

        if (! is_string($text) || trim($text) === '') {
            throw new NovaAiException('Mistral response contained no message content.');
        }

        return trim($text);
    }
}