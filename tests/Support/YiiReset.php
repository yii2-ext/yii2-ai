<?php

declare(strict_types=1);

namespace proweb\ai\tests\Support;

use ReflectionProperty;
use Yii;

/**
 * Resets the static Yii::$app property.
 *
 * The yii2 PHPStan extension types the property as non-null, so assigning null directly in
 * tests is a static error; reflection sidesteps the typed property.
 */
final class YiiReset
{
    private function __construct() {}

    public static function app(): void
    {
        $property = new ReflectionProperty(Yii::class, 'app');
        $property->setValue(null, null);
    }
}
