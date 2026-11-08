# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## [0.1.0] - 2026-11-08

### Added

- `ProviderFactory` with presets for Yandex AI Studio, OpenAI, Claude, Gemini, DeepSeek, Grok, and any
  OpenAI-compatible endpoint.
- `ChatService` / `ChatServiceInterface`: one-shot generation, streaming, multi-turn chat with caller-supplied
  history, model listing.
- `HistoryProviderInterface` for application-side conversation history.
- `AiComponent` and `Bootstrap` for component/DI wiring.
- Console commands `ai/chat` and `ai/list-models`.
- Agent skill (`skills/yii2-ai`) shipped with the package.

[0.1.0]: https://github.com/yii2-ext/yii2-ai/releases/tag/v0.1.0
