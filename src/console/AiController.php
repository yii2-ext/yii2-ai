<?php

declare(strict_types=1);

namespace proweb\ai\console;

use AIAccess\Model;
use proweb\ai\ChatServiceInterface;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Console commands for AI smoke tests.
 *
 * ```
 * php yii ai/list-models
 * php yii ai/chat "Hello" --model=gpt://<folder>/<model>
 * ```
 */
class AiController extends Controller
{
    /**
     * Model override for the chat action.
     */
    public string|null $model = null;

    public function __construct(
        $id,
        $module,
        private readonly ChatServiceInterface $chatService,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Asks the model a question and prints the answer.
     */
    public function actionChat(string $message): int
    {
        $response = $this->chatService->generate($message, $this->model);
        $text = $response->getText();
        if ($text === '') {
            $this->stderr(sprintf(
                "The model returned no text (finish reason: %s). A reasoning model may have spent the token limit on internal reasoning.\n",
                $response->getFinishReason()->value,
            ));

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout($text . "\n");

        return ExitCode::OK;
    }

    /**
     * Lists the models the backend offers.
     */
    public function actionListModels(): int
    {
        $models = $this->chatService->listModels();
        if ($models === []) {
            $this->stdout("The model list is empty (check the API key and folder).\n");

            return ExitCode::OK;
        }

        $this->stdout(implode("\n", array_map(static fn(Model $model): string => $model->id, $models)) . "\n");

        return ExitCode::OK;
    }

    /**
     * @param string $actionID
     */
    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), ['model']);
    }
}
