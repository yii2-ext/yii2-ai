<?php

declare(strict_types=1);

namespace proweb\ai;

use AIAccess\Chat\Chat;
use AIAccess\Chat\Response;
use AIAccess\Chat\Role;
use AIAccess\Model;
use Closure;
use InvalidArgumentException;

/**
 * High-level chat operations: one-shot generation, streaming, multi-turn history.
 */
final readonly class ChatService implements ChatServiceInterface
{
    public function __construct(
        private ChatProviderInterface $provider,
    ) {}

    public function chat(
        string $conversationId,
        string $prompt,
        HistoryProviderInterface $historyProvider,
        string|null $model = null,
        string|null $system = null,
    ): Response {
        return $this->chatWithHistory($historyProvider->getHistory($conversationId), $prompt, $model, $system);
    }

    /**
     * @param list<array{role: string, content: string}> $history
     */
    public function chatWithHistory(array $history, string $prompt, string|null $model = null, string|null $system = null): Response
    {
        $chat = $this->prepareChat($model, $system);
        foreach ($history as $message) {
            $chat->addMessage($message['content'], self::role($message['role']));
        }

        return $chat->sendMessage($prompt);
    }

    public function createChat(string|null $model = null): Chat
    {
        return $this->provider->createChat($model);
    }

    public function generate(string $prompt, string|null $model = null, string|null $system = null): Response
    {
        return $this->prepareChat($model, $system)->sendMessage($prompt);
    }

    /**
     * @return list<Model>
     */
    public function listModels(): array
    {
        return $this->provider->listModels();
    }

    /**
     * One-shot generation with streaming: $onDelta receives the answer in pieces as it is written.
     *
     * @param \Closure(string $prompt): (bool|null) $onDelta returning false stops the generation
     */
    public function stream(string $prompt, Closure $onDelta, string|null $model = null, string|null $system = null): Response
    {
        return $this->prepareChat($model, $system)->sendMessage($prompt, onStream: $onDelta);
    }

    private function prepareChat(string|null $model, string|null $system): Chat
    {
        $chat = $this->createChat($model);
        if ($system !== null) {
            $chat->setSystemInstruction($system);
        }

        return $chat;
    }

    private static function role(string $role): Role
    {
        return match ($role) {
            'user' => Role::User,
            'assistant', 'model' => Role::Model,
            default => throw new InvalidArgumentException(sprintf('Unsupported history role: %s.', $role)),
        };
    }
}
