<?php

declare(strict_types=1);

namespace proweb\ai;

use InvalidArgumentException;
use yii\base\Component;

/**
 * Application component wiring an AI provider into a [[ChatService]].
 *
 * ```php
 * 'components' => [
 *     'ai' => [
 *         'class' => \proweb\ai\AiComponent::class,
 *         'provider' => 'yandex',
 *         'apiKey' => env('YANDEX_CLOUD_API_KEY'),
 *         'model' => 'qwen3.6-35b-a3b',
 *         'folder' => env('YANDEX_CLOUD_FOLDER'),
 *         'temperature' => 0.3,
 *         'maxTokens' => 1024,
 *     ],
 * ],
 * ```
 *
 * With [[Bootstrap]] registered, the service is also available from the DI container as
 * [[ChatServiceInterface]].
 */
class AiComponent extends Component
{
    public const PROVIDER_CLAUDE = 'claude';
    public const PROVIDER_DEEPSEEK = 'deepseek';
    public const PROVIDER_GEMINI = 'gemini';
    public const PROVIDER_GROK = 'grok';
    public const PROVIDER_OPENAI = 'openai';
    public const PROVIDER_OPENAI_COMPATIBLE = 'openai-compatible';
    public const PROVIDER_YANDEX = 'yandex';

    public string|null $apiKey = null;

    /**
     * openai-compatible only: endpoint base URL.
     */
    public string|null $baseUrl = null;

    /**
     * Yandex only: folder id for the `gpt://` model URI.
     */
    public string|null $folder = null;

    public int|null $maxTokens = null;

    /**
     * Default model; required for the yandex provider, optional for the rest.
     */
    public string|null $model = null;

    /**
     * One of the PROVIDER_* constants.
     */
    public string $provider = self::PROVIDER_YANDEX;

    public float|null $temperature = null;

    public float|null $topP = null;

    private ChatService|null $_service = null;

    /**
     * The chat service backed by the configured provider.
     */
    public function chatService(): ChatService
    {
        return $this->_service ??= $this->createService();
    }

    private function createService(): ChatService
    {
        $provider = match ($this->provider) {
            self::PROVIDER_YANDEX => ProviderFactory::yandex(
                $this->requireApiKey(),
                $this->requireModel(),
                $this->folder,
                $this->baseUrl,
                $this->temperature,
                $this->maxTokens,
                $this->topP,
            ),
            self::PROVIDER_OPENAI_COMPATIBLE => ProviderFactory::openAiCompatible(
                $this->requireApiKey(),
                $this->requireBaseUrl(),
                $this->model,
                $this->temperature,
                $this->maxTokens,
                $this->topP,
            ),
            self::PROVIDER_OPENAI => ProviderFactory::openAi(
                $this->requireApiKey(),
                $this->model,
                $this->temperature,
                $this->maxTokens,
                $this->topP,
            ),
            self::PROVIDER_CLAUDE => ProviderFactory::claude(
                $this->requireApiKey(),
                $this->model,
                $this->temperature,
                $this->maxTokens,
                $this->topP,
            ),
            self::PROVIDER_GEMINI => ProviderFactory::gemini(
                $this->requireApiKey(),
                $this->model,
                $this->temperature,
                $this->maxTokens,
                $this->topP,
            ),
            self::PROVIDER_DEEPSEEK => ProviderFactory::deepSeek(
                $this->requireApiKey(),
                $this->model,
                $this->temperature,
                $this->maxTokens,
                $this->topP,
            ),
            self::PROVIDER_GROK => ProviderFactory::grok(
                $this->requireApiKey(),
                $this->model,
                $this->temperature,
                $this->maxTokens,
                $this->topP,
            ),
            default => throw new InvalidArgumentException(sprintf('Unknown AI provider: %s.', $this->provider)),
        };

        return new ChatService($provider);
    }

    private function requireApiKey(): string
    {
        return $this->apiKey ?? throw new InvalidArgumentException('AI component: apiKey is required.');
    }

    private function requireBaseUrl(): string
    {
        return $this->baseUrl ?? throw new InvalidArgumentException(
            'AI component: baseUrl is required for the openai-compatible provider.',
        );
    }

    private function requireModel(): string
    {
        return $this->model ?? throw new InvalidArgumentException('AI component: model is required.');
    }
}
