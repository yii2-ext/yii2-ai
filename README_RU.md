# Yii2 AI

< [English](README.md) | Русский >

[![Latest stable version](https://img.shields.io/packagist/v/proweb/yii2-ai.svg)](https://packagist.org/packages/proweb/yii2-ai)
[![PHP version](https://img.shields.io/packagist/php-v/proweb/yii2-ai.svg)](https://packagist.org/packages/proweb/yii2-ai)
[![Total downloads](https://img.shields.io/packagist/dt/proweb/yii2-ai.svg)](https://packagist.org/packages/proweb/yii2-ai)

Доступ к AI/LLM для [Yii2](https://github.com/yiisoft/yii2): фабрика провайдеров и сервис чата на базе
[ai-access](https://github.com/dg/ai-access).

Одна зависимость, семь бэкендов: Yandex AI Studio, OpenAI, Claude, Gemini, DeepSeek, Grok и любой OpenAI-совместимый
endpoint (Ollama, OpenRouter, vLLM, ...).

- Настройка провайдера одной строкой, пресеты под каждого
- Одноразовая генерация, стриминг и мультитёрн-чат с вашей историей
- Параметры генерации по умолчанию (temperature, max tokens, top-p) для каждого чата
- Список моделей напрямую с бэкенда
- Консольные smoke-команды (`ai/chat`, `ai/list-models`)
- DI-интеграция: компонент или определение контейнера — как удобно

> [!NOTE] Английская версия — в [README.md](README.md).

## Требования

- PHP 8.3 или выше
- Yii 2.0.54+ или ветка разработки Yii 22
- `ext-curl` (используется ai-access)

## Установка

```bash
composer require proweb/yii2-ai
```

## Быстрый старт

```php
use proweb\ai\ChatService;
use proweb\ai\ProviderFactory;

$service = new ChatService(
    ProviderFactory::yandex(
        apiKey: 'ваш-api-ключ',
        model: 'qwen3.6-35b-a3b',
        folder: 'ваш-folder-id',
        temperature: 0.3,
        maxTokens: 1024,
    ),
);

$response = $service->generate('Напиши хайку о PHP.');
echo $response->getText();
```

Или один раз подключить к приложению и брать из контейнера:

```php
// config/web.php (или console.php)
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

## Документация

- [Установка](docs/installation.md)
- [Конфигурация](docs/configuration.md)
- [Провайдеры](docs/providers.md)
- [Использование](docs/usage.md)
- [Тестирование](docs/testing.md)
- [AI-агенты](docs/agents.md): установка скилла, настройка клиентов и проверка.

[Скилл `yii2-ai`](skills/yii2-ai/SKILL.md) описывает настройку провайдеров, чат-сервис, консольные команды и конвенции
tестирования.

## Консольные команды

```bash
php yii ai/list-models
php yii ai/chat "Привет" --model=gpt://<folder>/<model>
```

Зарегистрируйте контроллер в `controllerMap` консоли и предоставьте определение `ChatServiceInterface` в контейнере (см.
[Конфигурация](docs/configuration.md)).

## Изменения

См. [CHANGELOG.md](CHANGELOG.md).

## Лицензия

[MIT](LICENSE). Расширение оборачивает [ai-access](https://github.com/dg/ai-access) (BSD-3-Clause) Дэвида Грудла.
