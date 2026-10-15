<?php

namespace App\Services\Nova\Providers;

use App\Exceptions\NovaAiException;
use App\Models\Child;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiNovaProvider extends AbstractNovaProvider
{
    public function name(): string
    {
        return 'gemini';
    }

    public function reply(Child $child, string $message, array $history): string
    {
        $apiKey = config('services.gemini.api_key');

        if (! $apiKey) {
            throw new NovaAiException(
                'Gemini API key is not configured (GEMINI_API_KEY).'
            );
        }

        $contents = [];

        foreach ($history as $turn) {
            $contents[] = [
                'role' => $turn['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $turn['content']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        try {
            $response = Http::timeout(20)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'
                    .config('services.gemini.model', 'gemini-2.5-flash')
                    .':generateContent?key='.urlencode($apiKey),
                [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $this->buildSystemPrompt($child)],
                        ],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'maxOutputTokens' => config('learnquest.nova.max_tokens', 300),
                    ],
                ]
            );
        } catch (Throwable $e) {
            Log::warning('Nova (Gemini) request failed', [
                'error' => $e->getMessage(),
            ]);

            throw new NovaAiException('Could not reach Gemini.', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Nova (Gemini) returned an error', [
                'status' => $response->status(),
            ]);

            throw new NovaAiException(
                "Gemini API responded with status {$response->status()}."
            );
        }

        $text = collect($response->json('candidates.0.content.parts', []))
            ->pluck('text')
            ->filter()
            ->implode("\n");

        if (trim($text) === '') {
            throw new NovaAiException('Gemini response contained no text content.');
        }

        return trim($text);
    }
}
