# Configuration

There are two ways to get a `ChatService` in the application.

## Container definition (recommended)

Build the service where the dependencies live — `config/services.php` or any config that feeds `container.definitions`:

```php
use proweb\ai\ChatService;
use proweb\ai\ChatServiceInterface;
use proweb\ai\ProviderFactory;

return [
    ChatServiceInterface::class => static function (): ChatService {
        return new ChatService(
            ProviderFactory::yandex(
                apiKey: env('YANDEX_CLOUD_API_KEY'),
                model: env('YANDEX_AI_MODEL', 'qwen3.6-35b-a3b'),
                folder: env('YANDEX_CLOUD_FOLDER'),
                temperature: 0.3,
                maxTokens: 1024,
            ),
        );
    },
];
```

Then inject the interface anywhere:

```php
final readonly class MyService
{
    public function __construct(
        private ChatServiceInterface $ai,
    ) {
    }
}
```

## Application component

Configure the `ai` component and register the bootstrap:

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
        'temperature' => 0.3,
        'maxTokens' => 1024,
    ],
],
```

`Bootstrap` registers `ChatServiceInterface` in `Yii::$container`, so both access styles work:

```php
$service = Yii::$app->ai->chatService();
$service = Yii::$container->get(\proweb\ai\ChatServiceInterface::class);
```

Component properties:

| Property      | Type      | Description                                                  |
| ------------- | --------- | ------------------------------------------------------------ |
| `provider`    | `string`  | One of the `AiComponent::PROVIDER_*` constants               |
| `apiKey`      | `?string` | API key (required)                                           |
| `model`       | `?string` | Default model (required for `yandex`, optional for the rest) |
| `folder`      | `?string` | Yandex folder id for the `gpt://` model URI                  |
| `baseUrl`     | `?string` | Endpoint base URL (required for `openai-compatible`)         |
| `temperature` | `?float`  | Default temperature applied to every chat                    |
| `maxTokens`   | `?int`    | Default max output tokens applied to every chat              |
| `topP`        | `?float`  | Default top-p applied to every chat                          |

## Console commands

The `ai` console controller takes the service through constructor injection, so a container definition is enough:

```php
// config/console.php
'controllerMap' => [
    'ai' => \proweb\ai\console\AiController::class,
],
```

```bash
php yii ai/list-models
php yii ai/chat "Hello" --model=gpt://<folder>/<model>
```

[Back to documentation](../README.md#documentation)
