<?php

namespace App\Services\Nova\Providers;

use App\Exceptions\NovaAiException;
use App\Models\Child;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiNovaProvider extends AbstractNovaProvider
{
    private const ATTEMPTS_PER_MODEL = 2;

    private const RETRYABLE_STATUSES = [
        408,
        429,
        500,
        502,
        503,
        504,
    ];

    public function name(): string
    {
        return 'gemini';
    }

    public function reply(
        Child $child,
        string $message,
        array $history
    ): string {
        $apiKey = config('services.gemini.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new NovaAiException(
                'Gemini API key is not configured (GEMINI_API_KEY).'
            );
        }

        $primaryModel = config(
            'services.gemini.model',
            'gemini-3.5-flash-lite'
        );

        $fallbackModel = config(
            'services.gemini.fallback_model',
            'gemini-3.6-flash'
        );

        $models = array_values(array_unique(array_filter(
            [$primaryModel, $fallbackModel],
            fn($model) => is_string($model)
                && trim($model) !== ''
        )));

        $contents = [];

        foreach ($history as $turn) {
            $contents[] = [
                'role' => $turn['role'] === 'assistant'
                    ? 'model'
                    : 'user',
                'parts' => [
                    ['text' => $turn['content']],
                ],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $message],
            ],
        ];

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $this->buildSystemPrompt($child)],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => config(
                    'learnquest.nova.max_tokens',
                    1024
                ),
                'thinkingConfig' => [
                    'thinkingLevel' => 'minimal',
                ],
            ],
        ];

        $lastStatus = null;
        $lastError = null;
        $lastConnectionException = null;

        foreach ($models as $model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
                . $model
                . ':generateContent';

            for (
                $attempt = 1;
                $attempt <= self::ATTEMPTS_PER_MODEL;
                $attempt++
            ) {
                try {
                    $response = Http::withHeaders([
                        'x-goog-api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                        ->connectTimeout(5)
                        ->timeout(20)
                        ->post($url, $payload);
                } catch (ConnectionException $e) {
                    $lastConnectionException = $e;
                    $lastStatus = null;
                    $lastError = 'Connection failure';

                    if ($attempt < self::ATTEMPTS_PER_MODEL) {
                        $this->waitBeforeRetry($attempt);

                        continue;
                    }

                    Log::warning(
                        'Nova (Gemini) model connection failed',
                        [
                            'model' => $model,
                            'attempts' => $attempt,
                            'error' => $e->getMessage(),
                        ]
                    );

                    // Try the next configured model.
                    break;
                }

                if ($response->successful()) {
                    $text = collect(
                        $response->json(
                            'candidates.0.content.parts',
                            []
                        )
                    )
                        ->pluck('text')
                        ->filter(
                            fn($part) => is_string($part)
                                && trim($part) !== ''
                        )
                        ->implode("\n");

                    if (trim($text) !== '') {
                        return trim($text);
                    }

                    $lastStatus = $response->status();
                    $lastError = 'Response contained no text content';

                    Log::warning(
                        'Nova (Gemini) returned no text content',
                        [
                            'model' => $model,
                            'finish_reason' => $response->json(
                                'candidates.0.finishReason'
                            ),
                            'prompt_feedback_reason' => $response->json(
                                'promptFeedback.blockReason'
                            ),
                        ]
                    );

                    // Try the next model if the response is empty.
                    break;
                }

                $lastStatus = $response->status();
                $lastError = $response->json('error.message')
                    ?? 'Gemini request failed';

                $retryable = in_array(
                    $lastStatus,
                    self::RETRYABLE_STATUSES,
                    true
                );

                if (
                    $retryable
                    && $attempt < self::ATTEMPTS_PER_MODEL
                ) {
                    $this->waitBeforeRetry($attempt);

                    continue;
                }

                Log::warning(
                    'Nova (Gemini) model request failed',
                    [
                        'model' => $model,
                        'status' => $lastStatus,
                        'attempts' => $attempt,
                        'api_error_code' => $response->json(
                            'error.status'
                        ),
                        'api_error_message' => $lastError,
                    ]
                );

                // Invalid credentials affect both models.
                if (in_array($lastStatus, [401, 403], true)) {
                    throw new NovaAiException(
                        'Gemini authentication or access failed.'
                    );
                }

                // Try the next model for other failures.
                break;
            }
        }

        Log::warning(
            'Nova (Gemini) exhausted configured models',
            [
                'models_tried' => $models,
                'last_status' => $lastStatus,
                'last_error' => $lastError,
            ]
        );

        throw new NovaAiException(
            'All configured Gemini models failed.'
                . ($lastStatus !== null
                    ? " Last HTTP status: {$lastStatus}."
                    : ''),
            previous: $lastConnectionException
        );
    }

    /**
     * Exponential backoff with jitter for transient failures.
     */
    private function waitBeforeRetry(int $attempt): void
    {
        $baseDelay = 1_000_000 * (2 ** ($attempt - 1));
        $jitter = random_int(0, 250_000);

        usleep($baseDelay + $jitter);
    }
}
