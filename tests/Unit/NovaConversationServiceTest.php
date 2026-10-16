<?php

namespace Tests\Unit;

use App\Contracts\Ai\NovaAiProviderContract;
use App\Exceptions\NovaAiException;
use App\Models\Child;
use App\Services\Nova\NovaConversationService;
use App\Services\Nova\NovaProviderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class NovaConversationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_provider_success_does_not_call_fallback(): void
    {
        $child = Child::factory()->create();

        $primary = Mockery::mock(NovaAiProviderContract::class);
        $primary->shouldReceive('name')
            ->andReturn('anthropic');

        $primary->shouldReceive('reply')
            ->once()
            ->with($child, 'Explain gravity', [])
            ->andReturn('Gravity pulls objects together.');

        $factory = Mockery::mock(NovaProviderFactory::class);
        $factory->shouldNotReceive('make');

        config([
            'learnquest.nova.fallback_providers' => [],
        ]);

        $service = new NovaConversationService($primary, $factory);

        $reply = $service->respond($child, 'Explain gravity');

        $this->assertSame(
            'Gravity pulls objects together.',
            $reply
        );

        $this->assertDatabaseHas('nova_messages', [
            'child_id' => $child->id,
            'role' => 'user',
            'content' => 'Explain gravity',
        ]);

        $this->assertDatabaseHas('nova_messages', [
            'child_id' => $child->id,
            'role' => 'assistant',
            'content' => 'Gravity pulls objects together.',
            'provider' => 'anthropic',
        ]);

        $this->assertSame(2, $child->novaMessages()->count());
    }

    public function test_fallback_provider_is_used_when_primary_fails(): void
    {
        $child = Child::factory()->create();

        $primary = Mockery::mock(NovaAiProviderContract::class);
        $primary->shouldReceive('name')
            ->andReturn('anthropic');

        $primary->shouldReceive('reply')
            ->once()
            ->andThrow(new NovaAiException('Primary provider unavailable.'));

        $fallback = Mockery::mock(NovaAiProviderContract::class);
        $fallback->shouldReceive('name')
            ->andReturn('openai');

        $fallback->shouldReceive('reply')
            ->once()
            ->with($child, 'What is a planet?', [])
            ->andReturn('A planet orbits a star.');

        $factory = Mockery::mock(NovaProviderFactory::class);
        $factory->shouldReceive('make')
            ->once()
            ->with('openai')
            ->andReturn($fallback);

        config([
            'learnquest.nova.fallback_providers' => ['openai'],
        ]);

        $service = new NovaConversationService($primary, $factory);

        $reply = $service->respond($child, 'What is a planet?');

        $this->assertSame('A planet orbits a star.', $reply);

        $this->assertDatabaseHas('nova_messages', [
            'child_id' => $child->id,
            'role' => 'assistant',
            'content' => 'A planet orbits a star.',
            'provider' => 'openai',
        ]);

        $this->assertSame(2, $child->novaMessages()->count());
    }

    public function test_safe_fallback_message_is_returned_when_all_providers_fail(): void
    {
        $child = Child::factory()->create();

        $primary = Mockery::mock(NovaAiProviderContract::class);
        $primary->shouldReceive('name')
            ->andReturn('anthropic');

        $primary->shouldReceive('reply')
            ->once()
            ->andThrow(new NovaAiException('Primary provider unavailable.'));

        $fallback = Mockery::mock(NovaAiProviderContract::class);
        $fallback->shouldReceive('name')
            ->andReturn('openai');

        $fallback->shouldReceive('reply')
            ->once()
            ->andThrow(new NovaAiException('Fallback provider unavailable.'));

        $factory = Mockery::mock(NovaProviderFactory::class);
        $factory->shouldReceive('make')
            ->once()
            ->with('openai')
            ->andReturn($fallback);

        config([
            'learnquest.nova.fallback_providers' => ['openai'],
        ]);

        $service = new NovaConversationService($primary, $factory);

        $reply = $service->respond($child, 'Help me learn.');

        $this->assertSame(
            'My circuits are a bit fuzzy right now 🤖💫 '
                . 'Can you try asking me again in a moment?',
            $reply
        );

        $this->assertDatabaseHas('nova_messages', [
            'child_id' => $child->id,
            'role' => 'assistant',
            'content' => $reply,
            'provider' => null,
        ]);

        $this->assertSame(2, $child->novaMessages()->count());
    }


    public function test_duplicate_provider_names_are_not_attempted_twice(): void
    {
        $child = Child::factory()->create();

        $primary = Mockery::mock(NovaAiProviderContract::class);

        $primary->shouldReceive('name')
            ->andReturn('openai');

        $primary->shouldReceive('reply')
            ->once()
            ->andThrow(
                new NovaAiException('Provider unavailable.')
            );

        $factory = Mockery::mock(NovaProviderFactory::class);

        $factory->shouldReceive('make')
            ->once()
            ->with('chatgpt')
            ->andReturnUsing(function () {
                $duplicate = Mockery::mock(
                    NovaAiProviderContract::class
                );

                $duplicate->shouldReceive('name')
                    ->andReturn('openai');

                return $duplicate;
            });

        config([
            'learnquest.nova.fallback_providers' => [
                'openai',
                'chatgpt',
            ],
        ]);

        $service = new NovaConversationService(
            $primary,
            $factory
        );

        $reply = $service->respond(
            $child,
            'Teach me something.'
        );

        $this->assertSame(
            'My circuits are a bit fuzzy right now 🤖💫 '
                . 'Can you try asking me again in a moment?',
            $reply
        );
    }


    public function test_openrouter_is_used_when_gemini_provider_fails(): void
    {
        $child = Child::factory()->create();

        $gemini = Mockery::mock(NovaAiProviderContract::class);

        $gemini->shouldReceive('name')
            ->andReturn('gemini');

        $gemini->shouldReceive('reply')
            ->once()
            ->with($child, 'Tell me a space fact.', [])
            ->andThrow(
                new NovaAiException(
                    'All configured Gemini models failed.'
                )
            );

        $openRouter = Mockery::mock(NovaAiProviderContract::class);

        $openRouter->shouldReceive('name')
            ->andReturn('openrouter');

        $openRouter->shouldReceive('reply')
            ->once()
            ->with($child, 'Tell me a space fact.', [])
            ->andReturn(
                'Mars has two tiny moons named Phobos and Deimos.'
            );

        $factory = Mockery::mock(NovaProviderFactory::class);

        $factory->shouldReceive('make')
            ->once()
            ->with('openrouter')
            ->andReturn($openRouter);

        config([
            'learnquest.nova.fallback_providers' => ['openrouter'],
        ]);

        $service = new NovaConversationService($gemini, $factory);

        $reply = $service->respond(
            $child,
            'Tell me a space fact.'
        );

        $this->assertSame(
            'Mars has two tiny moons named Phobos and Deimos.',
            $reply
        );

        $this->assertDatabaseHas('nova_messages', [
            'child_id' => $child->id,
            'role' => 'assistant',
            'content' => $reply,
            'provider' => 'openrouter',
        ]);

        $this->assertSame(2, $child->novaMessages()->count());
    }
}
