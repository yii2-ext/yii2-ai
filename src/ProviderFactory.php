<?php

declare(strict_types=1);

namespace proweb\ai;

use AIAccess\Http\Client as HttpClient;
use AIAccess\Http\CurlClient;
use AIAccess\Provider\Claude\Client as ClaudeClient;
use AIAccess\Provider\DeepSeek\Client as DeepSeekClient;
use AIAccess\Provider\Gemini\Client as GeminiClient;
use AIAccess\Provider\Grok\Client as GrokClient;
use AIAccess\Provider\OpenAI\Client as OpenAIClient;
use AIAccess\Provider\OpenAICompatible\Client as OpenAICompatibleClient;
use InvalidArgumentException;

/**
 * Builds chat providers for the supported LLM backends.
 *
 * Every method returns a ready-to-use [[ChatProviderInterface]]: the provider client is
 * constructed, the default model and generation options are set. Pass a custom
 * [[HttpClient]] (e.g. a mock or a retrying client) as the last argument when needed.
 */
final class ProviderFactory
{
    /**
     * Default base URL of the Yandex AI Studio OpenAI-compatible endpoint.
     */
    public const string YANDEX_BASE_URL = 'https://ai.api.cloud.yandex.net/v1';

    private function __construct() {}

    public static function claude(
        string $apiKey,
        string|null $model = null,
        float|null $temperature = null,
        int|null $maxTokens = null,
        float|null $topP = null,
        HttpClient|null $httpClient = null,
    ): ChatProviderInterface {
        $client = new ClaudeClient(
            $apiKey,
            $httpClient ?? new CurlClient(),
            chatModel: $model,
        );

        return new ChatProvider($client, $temperature, $maxTokens, $topP);
    }

    public static function deepSeek(
        string $apiKey,
        string|null $model = null,
        float|null $temperature = null,
        int|null $maxTokens = null,
        float|null $topP = null,
        HttpClient|null $httpClient = null,
    ): ChatProviderInterface {
        $client = new DeepSeekClient(
            $apiKey,
            $httpClient ?? new CurlClient(),
            chatModel: $model,
        );

        return new ChatProvider($client, $temperature, $maxTokens, $topP);
    }

    public static function gemini(
        string $apiKey,
        string|null $model = null,
        float|null $temperature = null,
        int|null $maxTokens = null,
        float|null $topP = null,
        HttpClient|null $httpClient = null,
    ): ChatProviderInterface {
        $client = new GeminiClient(
            $apiKey,
            $httpClient ?? new CurlClient(),
            chatModel: $model,
        );

        return new ChatProvider($client, $temperature, $maxTokens, $topP);
    }

    public static function grok(
        string $apiKey,
        string|null $model = null,
        float|null $temperature = null,
        int|null $maxTokens = null,
        float|null $topP = null,
        HttpClient|null $httpClient = null,
    ): ChatProviderInterface {
        $client = new GrokClient(
            $apiKey,
            $httpClient ?? new CurlClient(),
            chatModel: $model,
        );

        return new ChatProvider($client, $temperature, $maxTokens, $topP);
    }

    public static function openAi(
        string $apiKey,
        string|null $model = null,
        float|null $temperature = null,
        int|null $maxTokens = null,
        float|null $topP = null,
        HttpClient|null $httpClient = null,
    ): ChatProviderInterface {
        $client = new OpenAIClient(
            $apiKey,
            $httpClient ?? new CurlClient(),
            chatModel: $model,
        );

        return new ChatProvider($client, $temperature, $maxTokens, $topP);
    }

    /**
     * Any backend speaking the OpenAI chat-completions dialect (Ollama, OpenRouter, vLLM, ...).
     *
     * @param string $apiKey API key; an empty string disables the Authorization header
     */
    public static function openAiCompatible(
        string $apiKey,
        string $baseUrl,
        string|null $model = null,
        float|null $temperature = null,
        int|null $maxTokens = null,
        float|null $topP = null,
        HttpClient|null $httpClient = null,
    ): ChatProviderInterface {
        $client = new OpenAICompatibleClient(
            $apiKey,
            $baseUrl,
            $httpClient ?? new CurlClient(),
            chatModel: $model,
        );

        return new ChatProvider($client, $temperature, $maxTokens, $topP);
    }

    /**
     * Yandex AI Studio: OpenAI-compatible endpoint, `Api-Key` auth, `gpt://` model URIs.
     *
     * @param string $model model name (e.g. `qwen3.6-35b-a3b`) or a full `gpt://<folder>/<model>` URI
     * @param string|null $folder folder id; required when $model is not a full URI
     */
    public static function yandex(
        string $apiKey,
        string $model,
        string|null $folder = null,
        string|null $baseUrl = null,
        float|null $temperature = null,
        int|null $maxTokens = null,
        float|null $topP = null,
        HttpClient|null $httpClient = null,
    ): ChatProviderInterface {
        $client = new OpenAICompatibleClient(
            $apiKey,
            $baseUrl ?? self::YANDEX_BASE_URL,
            $httpClient ?? new CurlClient(),
            chatModel: self::yandexModelUri($model, $folder),
        );
        $client->setOptions(authPrefix: 'Api-Key ');

        return new ChatProvider($client, $temperature, $maxTokens, $topP);
    }

    /**
     * Builds the `gpt://<folder>/<model>` model URI used by Yandex AI Studio.
     */
    public static function yandexModelUri(string $model, string|null $folder = null): string
    {
        if (str_starts_with($model, 'gpt://')) {
            return $model;
        }

        if ($folder === null || $folder === '') {
            throw new InvalidArgumentException(sprintf(
                'Yandex model %s is not a full gpt:// URI and no folder id was given.',
                $model,
            ));
        }

        return "gpt://{$folder}/{$model}";
    }
}
