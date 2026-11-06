# Usage

## One-shot generation

```php
$response = $service->generate('Write a haiku about PHP.');

echo $response->getText();          // the answer
echo $response->getReasoning();     // chain of thought, if the model produced one (nullable)
echo $response->getFinishReason();  // AIAccess\Chat\FinishReason enum
$usage = $response->getUsage();     // AIAccess\Chat\Usage (nullable)
```

`generate()` accepts an optional model override and a system instruction:

```php
$response = $service->generate('Translate to Russian: "Hello"', model: 'gpt://f/other', system: 'You are a translator.');
```

## Streaming

```php
$response = $service->stream('Tell me a story', function (string $delta): ?bool {
    echo $delta; // pieces of the answer as they arrive
    return null; // returning false stops the generation
});
```

The returned `Response` carries the fully assembled answer.

## Multi-turn chat

History is your data: pass it as a list of `['role' => ..., 'content' => ...]` pairs, oldest first. Roles: `user`, `assistant` (or `model`).

```php
$response = $service->chatWithHistory(
    [
        ['role' => 'user', 'content' => 'What is 1+1?'],
        ['role' => 'assistant', 'content' => '2'],
    ],
    'And 2+1?',
);
```

### History from the database

Implement `HistoryProviderInterface` (typically a repository over the chat message table) and use `chat()`:

```php
final class ChatHistoryProvider implements HistoryProviderInterface
{
    public function __construct(
        private AiChatRepository $repository,
    ) {
    }

    public function getHistory(string $conversationId): array
    {
        return array_map(
            static fn (AiChatMessage $message): array => [
                'role' => $message->role === 'user' ? 'user' : 'assistant',
                'content' => $message->content,
            ],
            $this->repository->findByConversation($conversationId),
        );
    }
}

$response = $service->chat('conv-42', 'Continue', $historyProvider);
```

## Direct chat access

`createChat()` returns the underlying ai-access `Chat` object for everything the service does not wrap — tools, per-call options, message inspection:

```php
$chat = $service->createChat();
$chat->setSystemInstruction('You are a JSON extractor.');
$chat->setOptions(maxOutputTokens: 512);          // overrides the provider defaults for this chat
$chat->addTool(new \AIAccess\Chat\Tool('get_weather', 'Returns the weather.', ['location' => 'string']));

$response = $chat->sendMessage('Weather in Moscow?');
```

The provider default options (temperature, max tokens, top-p) are already applied to the chat; per-call `setOptions()` values win.

## Listing models

```php
foreach ($service->listModels() as $model) {
    echo $model->id, "\n";        // e.g. gpt://<folder>/<model>/latest
    // $model->raw — the full backend metadata
}
```

## Errors

ai-access throws a typed exception hierarchy on failure; the root question is "should I retry?":

- `AIAccess\ApiException` — the backend answered with an error status (message carries the backend reason)
- `AIAccess\CommunicationException` — connection problems, timeouts, invalid responses
- `AIAccess\LogicException` — misuse of the library

[Back to documentation](../README.md#documentation)
