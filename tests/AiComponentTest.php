<?php

declare(strict_types=1);

namespace proweb\ai\tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use proweb\ai\AiComponent;

#[CoversClass(AiComponent::class)]
final class AiComponentTest extends TestCase
{
    public function testMissingApiKeyIsRejected(): void
    {
        $component = new AiComponent([
            'provider' => AiComponent::PROVIDER_YANDEX,
            'model' => 'test-model',
            'folder' => 'test-folder',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('apiKey is required');

        $component->chatService();
    }

    public function testOpenAiCompatibleRequiresBaseUrl(): void
    {
        $component = new AiComponent([
            'provider' => AiComponent::PROVIDER_OPENAI_COMPATIBLE,
            'apiKey' => 'test-key',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('baseUrl is required');

        $component->chatService();
    }

    public function testUnknownProviderIsRejected(): void
    {
        $component = new AiComponent([
            'provider' => 'unknown',
            'apiKey' => 'test-key',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown AI provider: unknown');

        $component->chatService();
    }
    public function testYandexComponentBuildsChatService(): void
    {
        $component = new AiComponent([
            'provider' => AiComponent::PROVIDER_YANDEX,
            'apiKey' => 'test-key',
            'model' => 'test-model',
            'folder' => 'test-folder',
        ]);

        $service = $component->chatService();

        self::assertSame($service, $component->chatService(), 'the service must be cached');
    }

    /**
     * @return array<string, array{0: string, 1: array<string, mixed>}>
     */
    public static function providerConfigs(): array
    {
        return [
            'yandex' => [AiComponent::PROVIDER_YANDEX, ['apiKey' => 'test-key', 'model' => 'test-model', 'folder' => 'test-folder']],
            'openai' => [AiComponent::PROVIDER_OPENAI, ['apiKey' => 'test-key', 'model' => 'test-model']],
            'claude' => [AiComponent::PROVIDER_CLAUDE, ['apiKey' => 'test-key', 'model' => 'test-model']],
            'gemini' => [AiComponent::PROVIDER_GEMINI, ['apiKey' => 'test-key', 'model' => 'test-model']],
            'deepseek' => [AiComponent::PROVIDER_DEEPSEEK, ['apiKey' => 'test-key', 'model' => 'test-model']],
            'grok' => [AiComponent::PROVIDER_GROK, ['apiKey' => 'test-key', 'model' => 'test-model']],
            'openai-compatible' => [AiComponent::PROVIDER_OPENAI_COMPATIBLE, ['apiKey' => 'test-key', 'baseUrl' => 'https://example.test/v1', 'model' => 'test-model']],
        ];
    }

    /**
     * @param array<string, mixed> $config
     */
    #[DataProvider('providerConfigs')]
    public function testEachProviderBuildsACachedChatService(string $provider, array $config): void
    {
        $component = new AiComponent(['provider' => $provider] + $config);

        $service = $component->chatService();

        self::assertSame($service, $component->chatService(), 'the service must be cached');
    }

    public function testYandexRequiresModel(): void
    {
        $component = new AiComponent([
            'provider' => AiComponent::PROVIDER_YANDEX,
            'apiKey' => 'test-key',
            'folder' => 'test-folder',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('model is required');

        $component->chatService();
    }
}
