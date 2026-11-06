<?php

declare(strict_types=1);

namespace proweb\ai;

use Yii;
use yii\base\BootstrapInterface;

/**
 * Registers the chat service in the DI container when the `ai` component is configured.
 *
 * Add the class to the application bootstrap:
 *
 * ```php
 * 'bootstrap' => ['log', \proweb\ai\Bootstrap::class],
 * ```
 *
 * After that `Yii::$container->get(\proweb\ai\ChatServiceInterface::class)` works in any
 * context (controllers, services, console).
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app): void
    {
        $ai = $app->has('ai') ? $app->get('ai') : null;
        if ($ai instanceof AiComponent) {
            Yii::$container->set(ChatServiceInterface::class, static fn(): ChatServiceInterface => $ai->chatService());
        }
    }
}
