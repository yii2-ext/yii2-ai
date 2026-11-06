---
name: yii2-ai
description: Use when adding or changing AI/LLM access (Yandex AI Studio, OpenAI, Claude, Gemini, DeepSeek, Grok, or any OpenAI-compatible endpoint) in a Yii2 application using proweb/yii2-ai.
license: MIT
metadata:
  guide: https://github.com/yii2-ext/yii2-ai
---

# AI/LLM access in Yii2

Use `proweb\ai` for provider setup and chat. The package wraps [ai-access](https://github.com/dg/ai-access)
(BSD-3-Clause) and supports Yii 2.0.54+ and the Yii 22 development branch on PHP 8.3+.

## Read the relevant guide

In a root package checkout, use `composer show --self --format=json` for metadata. For installed dependencies, resolve
the real path and read the configured vendor directory's `composer/installed.php` (`versions[proweb/yii2-ai]`):
`install_path`, `pretty_version` and Composer's selected `reference`. For any live package checkout, including a
symlinked path install, inspect its current `HEAD` and files even when clean; report metadata mismatches and let the
actual files govern. Otherwise use the selected reference or a verified matching tag only after establishing its source
provenance. Compare `composer.lock`; it may differ from the inspected installation.

Prefer the checkout `docs/` (installation, configuration, providers, usage, testing). If docs are unavailable or
offline, inspect local source/PHPDoc and the changelog, and report the gap. Do not infer a signature from another AI
wrapper or copy a full API catalogue into the task.

## Core API

- `proweb\ai\ProviderFactory` — static presets: `yandex()`, `openAiCompatible()`, `openAi()`, `claude()`, `gemini()`,
  `deepSeek()`, `grok()`. Each returns a `ChatProviderInterface`; trailing options: `temperature`, `maxTokens`, `topP`,
  `httpClient` (mock/retry transport for tests).
- `proweb\ai\ChatService` (final readonly, implements `ChatServiceInterface`) — `generate()`, `stream()`,
  `chatWithHistory()`, `chat()`, `createChat()`, `listModels()`.
- `proweb\ai\HistoryProviderInterface` — application-side conversation history (repository over the chat table).
- `proweb\ai\AiComponent` + `proweb\ai\Bootstrap` — component/DI wiring; the bootstrap registers `ChatServiceInterface`
  in `Yii::$container`.
- `proweb\ai\console\AiController` — `ai/chat`, `ai/list-models`; the service is injected through the container
  definition, not through `controllerMap` properties.

## Conventions

- Yandex: `model` is a `gpt://<folder>/<model>` URI; a bare model name plus `folder` is combined automatically. Auth is
  `Authorization: Api-Key <key>`.
- Constructor injection everywhere in services; `Yii::$app`/`Yii::$container` only in controllers, views and console
  entry points.
- Secrets come from the environment (`.env` via the app's `env()` helper), never from committed config.
- Reasoning models may return empty text with `finish_reason: token_limit`; check `Response::getReasoning()`.
- Tests: mock `AIAccess\Http\Client` (record requests, queue JSON/SSE responses) and pass it as `httpClient`.
