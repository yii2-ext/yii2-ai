<?php

declare(strict_types=1);

namespace proweb\ai\tests\console;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use proweb\ai\ChatService;
use proweb\ai\ChatServiceInterface;
use proweb\ai\console\AiController;
use proweb\ai\ProviderFactory;
use proweb\ai\tests\Support\MockHttpClient;
use proweb\ai\tests\Support\YiiReset;
use yii\base\Exception;
use yii\console\Application;
use yii\console\ExitCode;

#[CoversClass(AiController::class)]
final class AiControllerTest extends TestCase
{
    public function testChatPrintsTheAnswer(): void
    {
        $http = new MockHttpClient();
        $http->queueResponse(200, [
            'choices' => [['index' => 0, 'message' => ['role' => 'assistant', 'content' => 'Hello from AI'], 'finish_reason' => 'stop']],
        ]);
        $controller = $this->controller($http);

        $exitCode = $controller->actionChat('Hi');

        self::assertSame(ExitCode::OK, $exitCode);
        self::assertStringContainsString('Hello from AI', $controller->stdoutText());
    }

    public function testChatSendsTheModelOption(): void
    {
        $http = new MockHttpClient();
        $http->queueResponse(200, [
            'choices' => [['index' => 0, 'message' => ['role' => 'assistant', 'content' => 'ok'], 'finish_reason' => 'stop']],
        ]);
        $controller = $this->controller($http);
        $controller->model = 'gpt://test-folder/other';

        $exitCode = $controller->actionChat('Hi');

        self::assertSame(ExitCode::OK, $exitCode);
        self::assertSame('gpt://test-folder/other', $http->lastJsonPayload()['model']);
    }

    public function testChatWithEmptyAnswerReportsTheFinishReason(): void
    {
        $http = new MockHttpClient();
        $http->queueResponse(200, [
            'choices' => [[
                'index' => 0,
                'message' => ['role' => 'assistant', 'content' => null, 'reasoning_content' => 'thinking'],
                'finish_reason' => 'length',
            ]],
        ]);
        $controller = $this->controller($http);

        $exitCode = $controller->actionChat('Hi');

        self::assertSame(ExitCode::UNSPECIFIED_ERROR, $exitCode);
        self::assertStringContainsString('token_limit', $controller->stderrText());
    }

    public function testChatWithoutTextFailsWithUsageError(): void
    {
        $controller = $this->controller(new MockHttpClient());

        try {
            $controller->runAction('chat', []);
            self::fail('Expected an exception for the missing message argument.');
        } catch (Exception $e) {
            self::assertStringContainsString('message', $e->getMessage());
        }
    }

    public function testControllerIsInstantiatedFromTheContainerDefinition(): void
    {
        $http = new MockHttpClient();
        $app = new Application([
            'id' => 'test',
            'basePath' => sys_get_temp_dir(),
            'container' => [
                'definitions' => [
                    ChatServiceInterface::class => new ChatService(
                        ProviderFactory::yandex('test-key', 'test-model', folder: 'test-folder', httpClient: $http),
                    ),
                ],
            ],
            'controllerMap' => [
                'ai' => AiController::class,
            ],
        ]);

        $created = $app->createController('ai');
        self::assertIsArray($created);
        [$controller] = $created;

        self::assertInstanceOf(AiController::class, $controller);
    }

    public function testListModelsPrintsTheModelIds(): void
    {
        $http = new MockHttpClient();
        $http->queueResponse(200, [
            'data' => [
                ['id' => 'gpt://test-folder/model-a'],
                ['id' => 'gpt://test-folder/model-b'],
            ],
        ]);
        $controller = $this->controller($http);

        $exitCode = $controller->actionListModels();

        self::assertSame(ExitCode::OK, $exitCode);
        self::assertStringContainsString('gpt://test-folder/model-a', $controller->stdoutText());
        self::assertStringContainsString('gpt://test-folder/model-b', $controller->stdoutText());
    }

    public function testListModelsWithEmptyList(): void
    {
        $http = new MockHttpClient();
        $http->queueResponse(200, ['data' => []]);
        $controller = $this->controller($http);

        $exitCode = $controller->actionListModels();

        self::assertSame(ExitCode::OK, $exitCode);
        self::assertStringContainsString('empty', $controller->stdoutText());
    }
    protected function tearDown(): void
    {
        YiiReset::app();

        parent::tearDown();
    }

    private function controller(MockHttpClient $http): CapturingAiController
    {
        $app = new Application([
            'id' => 'test',
            'basePath' => sys_get_temp_dir(),
        ]);

        return new CapturingAiController(
            'ai',
            $app,
            new ChatService(
                ProviderFactory::yandex('test-key', 'test-model', folder: 'test-folder', httpClient: $http),
            ),
        );
    }
}

/**
 * Test double capturing stdout/stderr instead of writing to the real streams.
 */
final class CapturingAiController extends AiController
{
    /**
     * @var string[]
     */
    private array $stderrLines = [];
    /**
     * @var string[]
     */
    private array $stdoutLines = [];

    public function stderr($string): int
    {
        $this->stderrLines[] = $string;

        return strlen($string);
    }

    public function stderrText(): string
    {
        return implode('', $this->stderrLines);
    }

    public function stdout($string): int
    {
        $this->stdoutLines[] = $string;

        return strlen($string);
    }

    public function stdoutText(): string
    {
        return implode('', $this->stdoutLines);
    }
}
