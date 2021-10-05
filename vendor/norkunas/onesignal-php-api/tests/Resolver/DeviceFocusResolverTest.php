<?php

declare(strict_types=1);

namespace OneSignal\Tests\Resolver;

use OneSignal\Resolver\DeviceFocusResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class DeviceFocusResolverTest extends TestCase
{
    /**
     * @var DeviceFocusResolver
     */
    private $deviceFocusResolver;

    protected function setUp(): void
    {
        $this->deviceFocusResolver = new DeviceFocusResolver();
    }

    public function testResolveWithValidValues(): void
    {
        $expectedData = [
            'state' => 'fakeState',
            'active_time' => 245,
        ];

        self::assertEquals($expectedData, $this->deviceFocusResolver->resolve($expectedData));
    }

    public function testResolveDefaultValues(): void
    {
        $expectedData = [
            'state' => 'ping',
            'active_time' => 23,
        ];

        self::assertEquals($expectedData, $this->deviceFocusResolver->resolve(['active_time' => 23]));
    }

    public function testResolveWithMissingRequiredValue(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->deviceFocusResolver->resolve([]);
    }

    public function wrongValueTypesProvider(): iterable
    {
        yield [['state' => 666]];
        yield [['active_time' => 'wrongType']];
    }

    /**
     * @dataProvider wrongValueTypesProvider
     */
    public function testResolveWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $requiredOptions = [
            'active_time' => 234,
        ];

        $this->deviceFocusResolver->resolve(array_merge($requiredOptions, $wrongOption));
    }

    public function testResolveWithWrongOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->deviceFocusResolver->resolve(['active_time' => 23, 'wrongOption' => 'wrongValue']);
    }
}
