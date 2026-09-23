<?php

namespace App\Contracts\Ai;

use App\Models\Child;
use App\Exceptions\NovaAiException;

/**
 * A Nova AI provider turns a child's message (plus recent chat
 * history and learning context) into Nova's reply.
 *
 * LearnQuest ships with an Anthropic (Claude) implementation as
 * the default engine. Additional providers — OpenAI, or any
 * other LLM vendor — only need to implement this contract and
 * register themselves in NovaProviderFactory to be selectable
 * via config('learnquest.nova.provider'), with no changes needed
 * anywhere else in the app.
 */
interface NovaAiProviderContract
{
    /**
     * Generate Nova's reply to the child's latest message.
     *
     * @param  Child  $child  The child Nova is talking to.
     * @param  string  $message  The child's new message.
     * @param  array<int, array{role: string, content: string}>  $history  Prior turns, oldest first.
     *
     * @throws NovaAiException  If the provider fails to respond.
     */
    public function reply(Child $child, string $message, array $history): string;

    /**
     * A short machine-readable name for this provider, stored
     * alongside each message for observability (e.g. "anthropic").
     */
    public function name(): string;
}
