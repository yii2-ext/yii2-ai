<?php

declare(strict_types=1);

namespace proweb\ai\tests;

use AIAccess\Provider\Claude\Client as ClaudeClient;
use AIAccess\Provider\DeepSeek\Client as DeepSeekClient;
use AIAccess\Provider\Gemini\Client as GeminiClient;
use AIAccess\Provider\Grok\Client as GrokClient;
use AIAccess\Provider\OpenAI\Client as OpenAIClient;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use proweb\ai\ChatProvider;
use proweb\ai\ChatProviderInterface;
use proweb\ai\ProviderFactory;
use proweb\ai\tests\Support\MockHttpClient;
use ReflectionProperty;

#[CoversClass(ProviderFactory::class)]
#[CoversClass(ChatProvider::class)]
final class ProviderFactoryTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: class-string}>
     */
    public static function clientProvider(): array
    {
        return [
            'openai' => ['openAi', OpenAIClient::class],
            'claude' => ['claude', ClaudeClient::class],
            'gemini' => ['gemini', GeminiClient::class],
            'deepseek' => ['deepSeek', DeepSeekClient::class],
            'grok' => ['grok', GrokClient::class],
        ];
    }

    public function testDefaultOptionsAreSentWithTheRequest(): void
    {
        $http = new MockHttpClient();
        $provider = ProviderFactory::yandex('test-key', 'm', folder: 'f', temperature: 0.3, maxTokens: 1024, topP: 0.9, httpClient: $http);

        $this->sendRequest($http, $provider);
        $payload = $http->lastJsonPayload();
        self::assertSame(0.3, $payload['temperature']);
        self::assertSame(1024, $payload['max_tokens']);
        self::assertSame(0.9, $payload['top_p']);
    }

    public function testNullDefaultOptionsAreNotSent(): void
    {
        $http = new MockHttpClient();
        $provider = ProviderFactory::openAiCompatible('test-key', 'https://example.test/v1', httpClient: $http);

        $this->sendRequest($http, $provider, 'some-model');
        $payload = $http->lastJsonPayload();
        self::assertArrayNotHasKey('temperature', $payload);
        self::assertArrayNotHasKey('max_tokens', $payload);
        self::assertArrayNotHasKey('top_p', $payload);
    }

    public function testOpenAiCompatibleUsesBearerAuthByDefault(): void
    {
        $http = new MockHttpClient();
        $provider = ProviderFactory::openAiCompatible('test-key', 'https://example.test/v1', 'some-model', httpClient: $http);

        $this->sendRequest($http, $provider);

        self::assertSame('Bearer test-key', $http->lastHeader('Authorization'));
    }

    /**
     * @param class-string $clientClass
     */
    #[DataProvider('clientProvider')]
    public function testProviderFactorsBuildTheRightClient(string $method, string $clientClass): void
    {
        $http = new MockHttpClient();
        $provider = match ($method) {
            'openAi' => ProviderFactory::openAi('test-key', 'some-model', httpClient: $http),
            'claude' => ProviderFactory::claude('test-key', 'some-model', httpClient: $http),
            'gemini' => ProviderFactory::gemini('test-key', 'some-model', httpClient: $http),
            'deepSeek' => ProviderFactory::deepSeek('test-key', 'some-model', httpClient: $http),
            'grok' => ProviderFactory::grok('test-key', 'some-model', httpClient: $http),
            default => throw new InvalidArgumentException("Unknown provider method: {$method}."),
        };

        self::assertInstanceOf(ChatProvider::class, $provider);

        $reflection = new ReflectionProperty(ChatProvider::class, 'client');
        self::assertInstanceOf($clientClass, $reflection->getValue($provider));
    }

    public function testYandexAcceptsFullModelUri(): void
    {
        $http = new MockHttpClient();
        $provider = ProviderFactory::yandex('test-key', 'gpt://folder-2/model-x/latest', httpClient: $http);

        $this->sendRequest($http, $provider);

        self::assertSame('gpt://folder-2/model-x/latest', $http->lastJsonPayload()['model']);
    }
    public function testYandexBuildsOpenAiCompatibleClientWithApiKeyAuth(): void
    {
        $http = new MockHttpClient();
        $provider = ProviderFactory::yandex('test-key', 'qwen3.6-35b-a3b', folder: 'folder-1', httpClient: $http);

        $this->sendRequest($http, $provider);
        $request = $http->lastRequest();
        self::assertNotNull($request);
        self::assertSame('https://ai.api.cloud.yandex.net/v1/chat/completions', $request['url']);
        self::assertSame('Api-Key test-key', $http->lastHeader('Authorization'));
        self::assertSame('gpt://folder-1/qwen3.6-35b-a3b', $http->lastJsonPayload()['model']);
    }

    public function testYandexCustomBaseUrl(): void
    {
        $http = new MockHttpClient();
        $provider = ProviderFactory::yandex('test-key', 'm', folder: 'f', baseUrl: 'https://example.test/v1', httpClient: $http);

        $this->sendRequest($http, $provider);
        $request = $http->lastRequest();
        self::assertNotNull($request);
        self::assertSame('https://example.test/v1/chat/completions', $request['url']);
    }

    public function testYandexModelUri(): void
    {
        self::assertSame('gpt://f/m', ProviderFactory::yandexModelUri('m', 'f'));
        self::assertSame('gpt://f/m/latest', ProviderFactory::yandexModelUri('gpt://f/m/latest'));
    }

    public function testYandexRequiresFolderForBareModelName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not a full gpt:// URI');

        ProviderFactory::yandex('test-key', 'qwen3.6-35b-a3b');
    }

    private function sendRequest(MockHttpClient $http, ChatProviderInterface $provider, string|null $model = null): void
    {
        $http->queueResponse(200, [
            'choices' => [['index' => 0, 'message' => ['role' => 'assistant', 'content' => 'ok'], 'finish_reason' => 'stop']],
        ]);
        $provider->createChat($model)->sendMessage('Hi');
    }
}
