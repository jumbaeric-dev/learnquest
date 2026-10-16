<?php

namespace App\Services\Nova\Providers;

use App\Exceptions\NovaAiException;
use App\Models\Child;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Http\Client\ConnectionException;

class OpenRouterNovaProvider extends AbstractNovaProvider
{
    private const ENDPOINT =
        'https://openrouter.ai/api/v1/chat/completions';

    private const DEFAULT_MODEL =
        'deepseek/deepseek-r1:free';

    public function name(): string
    {
        return 'openrouter';
    }

    public function reply(
        Child $child,
        string $message,
        array $history
    ): string {
        $apiKey = config('services.openrouter.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new NovaAiException(
                'OpenRouter API key is not configured (OPENROUTER_API_KEY).'
            );
        }

        $messages = [
            [
                'role' => 'system',
                'content' => $this->buildSystemPrompt($child),
            ],
        ];

        foreach ($history as $turn) {
            if (
                ! is_array($turn)
                || ! isset($turn['role'], $turn['content'])
                || ! is_string($turn['content'])
            ) {
                continue;
            }

            $messages[] = [
                'role' => $turn['role'] === 'assistant'
                    ? 'assistant'
                    : 'user',
                'content' => $turn['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        $model = config(
            'services.openrouter.model',
            self::DEFAULT_MODEL
        );

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'HTTP-Referer' => config(
                        'app.url',
                        'http://localhost'
                    ),
                    'X-OpenRouter-Title' => config(
                        'app.name',
                        'LearnQuest'
                    ),
                ])
                ->connectTimeout(5)
                ->timeout(30)
                ->post(self::ENDPOINT, [
                    'model' => $model,
                    'messages' => $messages,
                    'max_tokens' => config(
                        'learnquest.nova.max_tokens',
                        1024
                    ),
                ]);
        } catch (Throwable $e) {
            Log::warning('Nova (OpenRouter) request failed', [
                'model' => $model,
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);

            throw new NovaAiException(
                'Could not reach OpenRouter.',
                previous: $e
            );
        }

        if ($response->failed()) {
            Log::warning(
                'Nova (OpenRouter) returned an error',
                [
                    'status' => $response->status(),
                    'model' => $model,
                    'api_error_code' => $response->json(
                        'error.code'
                    ),
                    'api_error_message' => $response->json(
                        'error.message'
                    ),
                ]
            );

            throw new NovaAiException(
                "OpenRouter API responded with status {$response->status()}."
            );
        }

        Log::info('Nova (OpenRouter) response received', [
            'requested_model' => $model,
            'actual_model' => $response->json('model'),
            'finish_reason' => $response->json(
                'choices.0.finish_reason'
            ),
        ]);

        $text = $response->json(
            'choices.0.message.content'
        );

        if (! is_string($text) || trim($text) === '') {
            Log::warning(
                'Nova (OpenRouter) returned no text content',
                [
                    'model' => $model,
                    'finish_reason' => $response->json(
                        'choices.0.finish_reason'
                    ),
                ]
            );

            throw new NovaAiException(
                'OpenRouter response contained no message content.'
            );
        }

        return trim($text);
    }
}
