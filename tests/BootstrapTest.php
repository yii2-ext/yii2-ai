<?php

declare(strict_types=1);

namespace proweb\ai\tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use proweb\ai\AiComponent;
use proweb\ai\Bootstrap;
use proweb\ai\ChatService;
use proweb\ai\ChatServiceInterface;
use proweb\ai\tests\Support\YiiReset;
use Yii;
use yii\console\Application;
use yii\di\NotInstantiableException;

#[CoversClass(Bootstrap::class)]
final class BootstrapTest extends TestCase
{
    private Application|null $app = null;

    public function testDoesNothingWithoutTheAiComponent(): void
    {
        $this->app = new Application([
            'id' => 'test',
            'basePath' => sys_get_temp_dir(),
        ]);

        (new Bootstrap())->bootstrap($this->app);

        $this->expectException(NotInstantiableException::class);
        Yii::$container->get(ChatServiceInterface::class);
    }

    public function testRegistersChatServiceInTheContainer(): void
    {
        $this->app = new Application([
            'id' => 'test',
            'basePath' => sys_get_temp_dir(),
            'components' => [
                'ai' => [
                    'class' => AiComponent::class,
                    'provider' => AiComponent::PROVIDER_YANDEX,
                    'apiKey' => 'test-key',
                    'model' => 'test-model',
                    'folder' => 'test-folder',
                ],
            ],
        ]);

        (new Bootstrap())->bootstrap($this->app);

        $service = Yii::$container->get(ChatServiceInterface::class);
        self::assertInstanceOf(ChatService::class, $service);
    }

    protected function tearDown(): void
    {
        $this->app = null;
        YiiReset::app();
        Yii::$container->clear(ChatServiceInterface::class);

        parent::tearDown();
    }
}
