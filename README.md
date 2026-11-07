# Yii2 AI

AI/LLM access for [Yii2](https://github.com/yiisoft/yii2): a provider factory and a chat service on top of
[ai-access](https://github.com/dg/ai-access).

One dependency, seven backends: Yandex AI Studio, OpenAI, Claude, Gemini, DeepSeek, Grok, and any OpenAI-compatible
endpoint (Ollama, OpenRouter, vLLM, ...).

- One-line provider setup with per-provider presets
- One-shot generation, streaming, and multi-turn chat with your own history
- Default generation options (temperature, max tokens, top-p) applied to every chat
- Model listing straight from the backend
- Console smoke-test commands (`ai/chat`, `ai/list-models`)
- DI integration: component or container definition, your choice

> [!NOTE] A Russian version of this guide is available in [README_RU.md](README_RU.md).

## Requirements

- PHP 8.3 or higher
- Yii 2.0.54+ or the Yii 22 development branch
- `ext-curl` (used by ai-access)

## Installation

```bash
composer require proweb/yii2-ai
```

## Quick start

```php
use proweb\ai\ChatService;
use proweb\ai\ProviderFactory;

$service = new ChatService(
    ProviderFactory::yandex(
        apiKey: 'your-api-key',
        model: 'qwen3.6-35b-a3b',
        folder: 'your-folder-id',
        temperature: 0.3,
        maxTokens: 1024,
    ),
);

$response = $service->generate('Write a haiku about PHP.');
echo $response->getText();
```

Or wire it into the application once and pull it from the container:

```php
// config/web.php (or console.php)
'bootstrap' => ['log', \proweb\ai\Bootstrap::class],
'components' => [
    'ai' => [
        'class' => \proweb\ai\AiComponent::class,
        'provider' => 'yandex',
        'apiKey' => env('YANDEX_CLOUD_API_KEY'),
        'model' => 'qwen3.6-35b-a3b',
        'folder' => env('YANDEX_CLOUD_FOLDER'),
    ],
],
```

```php
$service = Yii::$container->get(\proweb\ai\ChatServiceInterface::class);
```

## Documentation

- [Installation guide](docs/installation.md)
- [Configuration](docs/configuration.md)
- [Providers](docs/providers.md)
- [Usage](docs/usage.md)
- [Testing](docs/testing.md)
- [AI agents](docs/agents.md): skill installation, client setup, and verification.

The [`yii2-ai` skill](skills/yii2-ai/SKILL.md) covers provider setup, the chat service, console commands, and testing
conventions.

## Console commands

```bash
php yii ai/list-models
php yii ai/chat "Hello" --model=gpt://<folder>/<model>
```

Register the controller in the console `controllerMap` and provide a `ChatServiceInterface` container definition (see
[Configuration](docs/configuration.md)).

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

[MIT](LICENSE). The extension wraps [ai-access](https://github.com/dg/ai-access) (BSD-3-Clause) by David Grudl.
