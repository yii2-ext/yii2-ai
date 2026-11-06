<?php

declare(strict_types=1);

namespace proweb\ai;

/**
 * Supplies conversation history to [[ChatService::chat()]].
 *
 * Implement it in the application, typically as a repository over the chat message table.
 */
interface HistoryProviderInterface
{
    /**
     * Conversation history, oldest message first.
     *
     * @return list<array{role: string, content: string}>
     */
    public function getHistory(string $conversationId): array;
}
