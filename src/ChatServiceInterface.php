<?php

declare(strict_types=1);

namespace proweb\ai;

use AIAccess\Chat\Chat;
use AIAccess\Chat\Response;
use AIAccess\Model;
use Closure;

/**
 * High-level chat operations on top of a [[ChatProviderInterface]].
 */
interface ChatServiceInterface
{
    /**
     * Multi-turn chat with the history loaded by $historyProvider.
     */
    public function chat(
        string $conversationId,
        string $prompt,
        HistoryProviderInterface $historyProvider,
        string|null $model = null,
        string|null $system = null,
    ): Response;

    /**
     * Multi-turn chat: the given history (oldest first) plus the new prompt.
     *
     * @param list<array{role: string, content: string}> $history
     */
    public function chatWithHistory(array $history, string $prompt, string|null $model = null, string|null $system = null): Response;
    /**
     * Creates a chat session with the provider's default options applied.
     * Configure it further (system instruction, tools, per-call options) and send messages.
     */
    public function createChat(string|null $model = null): Chat;

    /**
     * One-shot generation.
     */
    public function generate(string $prompt, string|null $model = null, string|null $system = null): Response;

    /**
     * Lists the models the provider's backend offers.
     *
     * @return list<Model>
     */
    public function listModels(): array;

    /**
     * One-shot generation with streaming: $onDelta receives the answer in pieces as it is written.
     *
     * @param \Closure(string $prompt): (bool|null) $onDelta returning false stops the generation
     */
    public function stream(string $prompt, Closure $onDelta, string|null $model = null, string|null $system = null): Response;
}
