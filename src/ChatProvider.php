<?php

declare(strict_types=1);

namespace proweb\ai;

use AIAccess\Chat\Chat;
use AIAccess\Model;
use AIAccess\Provider\Claude\Chat as ClaudeChat;
use AIAccess\Provider\Claude\Client as ClaudeClient;
use AIAccess\Provider\DeepSeek\Chat as DeepSeekChat;
use AIAccess\Provider\DeepSeek\Client as DeepSeekClient;
use AIAccess\Provider\Gemini\Chat as GeminiChat;
use AIAccess\Provider\Gemini\Client as GeminiClient;
use AIAccess\Provider\Grok\Chat as GrokChat;
use AIAccess\Provider\Grok\Client as GrokClient;
use AIAccess\Provider\OpenAI\Chat as OpenAIChat;
use AIAccess\Provider\OpenAI\Client as OpenAIClient;
use AIAccess\Provider\OpenAICompatible\Chat as OpenAICompatibleChat;
use AIAccess\Provider\OpenAICompatible\Client as OpenAICompatibleClient;
use LogicException;

/**
 * Wraps an ai-access provider client and applies the default generation options
 * to every chat session it creates.
 */
final readonly class ChatProvider implements ChatProviderInterface
{
    public function __construct(
        private OpenAIClient|ClaudeClient|GeminiClient|DeepSeekClient|GrokClient|OpenAICompatibleClient $client,
        private float|null $temperature = null,
        private int|null $maxTokens = null,
        private float|null $topP = null,
    ) {}

    public function createChat(string|null $model = null): Chat
    {
        $chat = $this->client->createChat($model);
        $this->applyOptions($chat);

        return $chat;
    }

    /**
     * @return list<Model>
     */
    public function listModels(): array
    {
        return $this->client->listModels();
    }

    /**
     * Applies the default options to the chat.
     *
     * Every provider chat exposes setOptions() with the named parameters maxOutputTokens,
     * temperature and topP, but at different positions. PHPStan 2.x cannot check named
     * arguments on a union of these signatures, so the call is narrowed to the concrete
     * type; the array unpacking passes the same named arguments in every branch.
     */
    private function applyOptions(Chat $chat): void
    {
        if ($this->temperature === null && $this->maxTokens === null && $this->topP === null) {
            return;
        }

        $options = [
            'maxOutputTokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'topP' => $this->topP,
        ];

        match (true) {
            $chat instanceof OpenAIChat => $chat->setOptions(...$options),
            $chat instanceof ClaudeChat => $chat->setOptions(...$options),
            $chat instanceof GeminiChat => $chat->setOptions(...$options),
            $chat instanceof DeepSeekChat => $chat->setOptions(...$options),
            $chat instanceof GrokChat => $chat->setOptions(...$options),
            $chat instanceof OpenAICompatibleChat => $chat->setOptions(...$options),
            default => throw new LogicException(sprintf('Unsupported chat type: %s.', get_debug_type($chat))),
        };
    }
}
