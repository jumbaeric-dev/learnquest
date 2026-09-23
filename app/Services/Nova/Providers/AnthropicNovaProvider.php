<?php

namespace App\Services\Nova\Providers;

use App\Exceptions\NovaAiException;
use App\Models\Child;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Default Nova engine, powered by Claude via Anthropic's Messages API.
 */
class AnthropicNovaProvider extends AbstractNovaProvider
{
    public function name(): string
    {
        return 'anthropic';
    }

    public function reply(Child $child, string $message, array $history): string
    {
        $apiKey = config('services.anthropic.api_key');

        if (! $apiKey) {
            throw new NovaAiException(
                'Anthropic API key is not configured (ANTHROPIC_API_KEY).'
            );
        }

        $messages = $this->toAnthropicMessages($history, $message);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
                ->timeout(20)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('services.anthropic.model', 'claude-sonnet-5'),
                    'max_tokens' => config('learnquest.nova.max_tokens', 300),
                    'system' => $this->buildSystemPrompt($child),
                    'messages' => $messages,
                ]);
        } catch (Throwable $e) {
            Log::warning('Nova (Anthropic) request failed', ['error' => $e->getMessage()]);

            throw new NovaAiException('Could not reach Claude.', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Nova (Anthropic) returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new NovaAiException(
                "Anthropic API responded with status {$response->status()}."
            );
        }

        $text = collect($response->json('content', []))
            ->firstWhere('type', 'text')['text'] ?? null;

        if (! $text) {
            throw new NovaAiException('Anthropic response contained no text content.');
        }

        return trim($text);
    }

    /**
     * Convert Nova's internal history shape into Anthropic's
     * messages format, appending the new user message.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<int, array{role: string, content: string}>
     */
    protected function toAnthropicMessages(array $history, string $message): array
    {
        $messages = collect($history)
            ->map(fn(array $turn) => [
                'role' => $turn['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $turn['content'],
            ])
            ->all();

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        return $messages;
    }
}
