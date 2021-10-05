<?php

declare(strict_types=1);

namespace OneSignal\Tests;

use OneSignal\Config;
use PHPUnit\Framework\TestCase;

abstract class OneSignalTestCase extends TestCase
{
    protected function createConfig(): Config
    {
        return new Config('fakeApplicationId', 'fakeApplicationAuthKey', 'fakeUserAuthKey');
    }
}
