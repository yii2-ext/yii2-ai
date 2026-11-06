<?php

declare(strict_types=1);

namespace proweb\ai\tests\Support;

use proweb\ai\HistoryProviderInterface;

/**
 * History provider recording the requested conversation id and returning a fixed history.
 */
final class RecordingHistoryProvider implements HistoryProviderInterface
{
    public string|null $requestedConversationId = null;

    public function getHistory(string $conversationId): array
    {
        $this->requestedConversationId = $conversationId;

        return [
            ['role' => 'user', 'content' => 'earlier'],
            ['role' => 'model', 'content' => 'reply'],
        ];
    }
}
