<?php

namespace App\Services\Nova;

use App\Contracts\Ai\NovaAiProviderContract;
use App\Services\Nova\Providers\AnthropicNovaProvider;
use App\Services\Nova\Providers\OpenAiNovaProvider;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class NovaProviderFactory
{
    /**
     * Map of config('learnquest.nova.provider') values to their
     * provider class. Add a new AI vendor by implementing
     * NovaAiProviderContract and adding one line here.
     *
     * @var array<string, class-string<NovaAiProviderContract>>
     */
    protected static array $providers = [
        'anthropic' => AnthropicNovaProvider::class,
        'claude' => AnthropicNovaProvider::class,
        'openai' => OpenAiNovaProvider::class,
        'chatgpt' => OpenAiNovaProvider::class,
    ];

    public function __construct(
        protected Container $container,
    ) {
    }

    public function make(?string $provider = null): NovaAiProviderContract
    {
        $key = strtolower(
            $provider ?? config('learnquest.nova.provider', 'anthropic')
        );

        $class = static::$providers[$key] ?? null;

        if (! $class) {
            throw new InvalidArgumentException(
                "Unknown Nova AI provider [{$key}]. Available: "
                    .implode(', ', array_keys(static::$providers))
            );
        }

        return $this->container->make($class);
    }
}
