<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Must be defined before the Yii class is loaded: BaseYii defaults it to true.
// Disabled in tests so exceptions propagate to the test code instead of being
// rendered and swallowed (or exiting the PHP process).
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);

// The global Yii class is a plain file the application entry script requires;
// require it explicitly when the autoloader does not know it.
if (!class_exists('Yii')) {
    require_once dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
}
