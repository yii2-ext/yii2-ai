<?php

declare(strict_types=1);

namespace proweb\ai\tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
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
