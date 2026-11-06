<?php

declare(strict_types=1);

namespace proweb\ai\tests;

use AIAccess\ApiException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use proweb\ai\ChatService;
use proweb\ai\ProviderFactory;
use proweb\ai\tests\Support\MockHttpClient;
use proweb\ai\tests\Support\RecordingHistoryProvider;

#[CoversClass(ChatService::class)]
final class ChatServiceTest extends TestCase
{
    private MockHttpClient $http;

    private ChatService $service;

    public function testApiErrorIsThrown(): void
    {
        $this->http->queueResponse(401, ['error' => ['message' => 'Invalid API key']]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid API key');

        $this->service->generate('Hi');
    }

    public function testChatLoadsHistoryFromProvider(): void
    {
        $historyProvider = new RecordingHistoryProvider();
        $this->http->queueResponse(200, self::completion('next'));

        $response = $this->service->chat('conv-42', 'continue', $historyProvider);

        self::assertSame('conv-42', $historyProvider->requestedConversationId);
        self::assertSame('next', $response->getText());
        $payload = $this->http->lastJsonPayload();
        self::assertSame(
            [
                ['role' => 'user', 'content' => 'earlier'],
                ['role' => 'assistant', 'content' => 'reply'],
                ['role' => 'user', 'content' => 'continue'],
            ],
            $payload['messages'],
        );
    }

    public function testChatWithHistorySendsHistoryInOrder(): void
    {
        $this->http->queueResponse(200, self::completion('3'));

        $response = $this->service->chatWithHistory(
            [
                ['role' => 'user', 'content' => 'What is 1+1?'],
                ['role' => 'assistant', 'content' => '2'],
            ],
            'And 2+1?',
        );

        self::assertSame('3', $response->getText());
        $payload = $this->http->lastJsonPayload();
        self::assertSame(
            [
                ['role' => 'user', 'content' => 'What is 1+1?'],
                ['role' => 'assistant', 'content' => '2'],
                ['role' => 'user', 'content' => 'And 2+1?'],
            ],
            $payload['messages'],
        );
    }

    public function testGenerateSendsPromptAndReturnsText(): void
    {
        $this->http->queueResponse(200, self::completion('Hello there'));

        $response = $this->service->generate('Hi');

        self::assertSame('Hello there', $response->getText());
        $payload = $this->http->lastJsonPayload();
        self::assertSame([['role' => 'user', 'content' => 'Hi']], $payload['messages']);
        self::assertSame('gpt://test-folder/test-model', $payload['model']);
    }

    public function testGenerateSendsSystemInstructionFirst(): void
    {
        $this->http->queueResponse(200, self::completion('ok'));

        $this->service->generate('Hi', system: 'Be brief.');

        $payload = $this->http->lastJsonPayload();
        self::assertSame(
            [
                ['role' => 'system', 'content' => 'Be brief.'],
                ['role' => 'user', 'content' => 'Hi'],
            ],
            $payload['messages'],
        );
    }

    public function testGenerateWithModelOverride(): void
    {
        $this->http->queueResponse(200, self::completion('ok'));

        $this->service->generate('Hi', model: 'gpt://test-folder/other-model');

        $payload = $this->http->lastJsonPayload();
        self::assertSame('gpt://test-folder/other-model', $payload['model']);
    }

    public function testListModels(): void
    {
        $this->http->queueResponse(200, [
            'data' => [
                ['id' => 'gpt://test-folder/model-a'],
                ['id' => 'gpt://test-folder/model-b'],
            ],
        ]);

        $models = $this->service->listModels();

        self::assertCount(2, $models);
        self::assertSame('gpt://test-folder/model-a', $models[0]->id);
        $request = $this->http->lastRequest();
        self::assertNotNull($request);
        self::assertSame('https://ai.api.cloud.yandex.net/v1/models', $request['url']);
    }

    public function testReasoningOnlyAnswerHasEmptyTextAndReasoning(): void
    {
        $this->http->queueResponse(200, [
            'id' => 'chatcmpl-1',
            'object' => 'chat.completion',
            'choices' => [[
                'index' => 0,
                'message' => ['role' => 'assistant', 'content' => null, 'reasoning_content' => 'thinking...'],
                'finish_reason' => 'length',
            ]],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 16, 'total_tokens' => 26],
        ]);

        $response = $this->service->generate('Hi');

        self::assertSame('', $response->getText());
        self::assertSame('thinking...', $response->getReasoning());
    }

    public function testStreamDeliversDeltasAndAssemblesTheAnswer(): void
    {
        $this->http->queueStream(
            "data: {\"choices\":[{\"index\":0,\"delta\":{\"role\":\"assistant\",\"content\":\"Good\"},\"finish_reason\":null}]}\n\n",
            "data: {\"choices\":[{\"index\":0,\"delta\":{\"content\":\"bye\"},\"finish_reason\":\"stop\"}]}\n\n",
            "data: [DONE]\n\n",
        );

        $deltas = [];
        $response = $this->service->stream('Hi', static function (string $delta) use (&$deltas): bool|null {
            $deltas[] = $delta;

            return null;
        });

        self::assertSame(['Good', 'bye'], $deltas);
        self::assertSame('Goodbye', $response->getText());
    }

    public function testStreamSendsStreamFlag(): void
    {
        $this->http->queueStream("data: [DONE]\n\n");

        $this->service->stream('Hi', static fn(string $delta): bool|null => null);

        $payload = $this->http->lastJsonPayload();
        self::assertTrue($payload['stream']);
    }

    public function testUnsupportedHistoryRoleIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported history role: system');

        $this->service->chatWithHistory([['role' => 'system', 'content' => 'x']], 'Hi');
    }

    protected function setUp(): void
    {
        $this->http = new MockHttpClient();
        $this->service = new ChatService(
            ProviderFactory::yandex('test-key', 'test-model', folder: 'test-folder', httpClient: $this->http),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function completion(string $text): array
    {
        return [
            'id' => 'chatcmpl-1',
            'object' => 'chat.completion',
            'choices' => [[
                'index' => 0,
                'message' => ['role' => 'assistant', 'content' => $text],
                'finish_reason' => 'stop',
            ]],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
        ];
    }
}
