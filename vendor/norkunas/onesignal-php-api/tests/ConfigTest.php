<?php

declare(strict_types=1);

namespace OneSignal\Tests;

class ConfigTest extends OneSignalTestCase
{
    public function testGetApplicationId(): void
    {
        self::assertSame('fakeApplicationId', ($this->createConfig())->getApplicationId());
    }

    public function testGetApplicationAuthKey(): void
    {
        self::assertSame('fakeApplicationAuthKey', ($this->createConfig())->getApplicationAuthKey());
    }

    public function testGetUserAuthKey(): void
    {
        self::assertSame('fakeUserAuthKey', ($this->createConfig())->getUserAuthKey());
    }
}
