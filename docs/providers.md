# Providers

`ProviderFactory` builds a ready-to-use `ChatProviderInterface` for each supported backend. Every method accepts the same trailing options:

```php
ProviderFactory::yandex(
    apiKey: '...',
    model: '...',
    // ...provider-specific arguments...
    temperature: 0.3,     // ?float, applied to every chat
    maxTokens: 1024,      // ?int, applied to every chat
    topP: 0.9,            // ?float, applied to every chat
    httpClient: null,     // ?AIAccess\Http\Client, e.g. a mock or a retrying client
);
```

## Yandex AI Studio

```php
ProviderFactory::yandex(
    apiKey: env('YANDEX_CLOUD_API_KEY'),
    model: 'qwen3.6-35b-a3b',          // or a full gpt://<folder>/<model> URI
    folder: env('YANDEX_CLOUD_FOLDER'), // required when model is not a full URI
    baseUrl: null,                      // default: https://ai.api.cloud.yandex.net/v1
);
```

- Endpoint: the OpenAI-compatible `chat/completions` dialect at `https://ai.api.cloud.yandex.net/v1`.
- Auth: `Authorization: Api-Key <key>`.
- Models: `gpt://<folder-id>/<model>` URIs; a bare model name plus `folder` is combined into the URI automatically.
- Reasoning models (e.g. `deepseek-v4-flash`) may spend the token budget on internal reasoning and return an empty text with `finish_reason: token_limit` — check `Response::getReasoning()`.

## OpenAI-compatible endpoints

```php
ProviderFactory::openAiCompatible(
    apiKey: '...',        // empty string disables the Authorization header
    baseUrl: 'http://127.0.0.1:11434/v1', // Ollama, OpenRouter, vLLM, ...
    model: 'llama3.1',    // optional; can be passed per chat instead
);
```

Uses `Bearer` auth by default.

## OpenAI, Claude, Gemini, DeepSeek, Grok

```php
ProviderFactory::openAi(apiKey: 'sk-...', model: 'gpt-4o');
ProviderFactory::claude(apiKey: 'sk-ant-...', model: 'claude-sonnet-4-5');
ProviderFactory::gemini(apiKey: '...', model: 'gemini-2.5-pro');
ProviderFactory::deepSeek(apiKey: 'sk-...', model: 'deepseek-chat');
ProviderFactory::grok(apiKey: 'xai-...', model: 'grok-3');
```

All five accept an optional default `model`; when it is not set, pass the model per chat:

```php
$service->createChat('gpt-4o')->sendMessage('Hello');
```

## Custom HTTP transport

Every factory method accepts an `AIAccess\Http\Client` as the last argument. The default is `CurlClient`; ai-access also ships `RetryClient`, `CachingClient` and `ObservableClient` (logging).

[Back to documentation](../README.md#documentation)
