<?php

declare(strict_types=1);

namespace OneSignal\Tests\Resolver;

use OneSignal\Resolver\DeviceSessionResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class DeviceSessionResolverTest extends TestCase
{
    /**
     * @var DeviceSessionResolver
     */
    private $deviceSessionResolver;

    protected function setUp(): void
    {
        $this->deviceSessionResolver = new DeviceSessionResolver();
    }

    public function testResolveWithValidValues(): void
    {
        $expectedData = [
            'identifier' => 'fakeIdentifier',
            'language' => 'fakeIdentifier',
            'timezone' => 234,
            'game_version' => 'fakeGameVersion',
            'device_os' => 'fakeDeviceOS',
            'device_model' => 'fakeDeviceModel',
            'ad_id' => 'fakeAdId',
            'sdk' => 'fakeSdk',
            'tags' => ['fakeTag'],
        ];

        self::assertEquals($expectedData, $this->deviceSessionResolver->resolve($expectedData));
    }

    public function wrongValueTypesProvider(): iterable
    {
        yield [['identifier' => 666]];
        yield [['language' => 666]];
        yield [['timezone' => 'wrongType']];
        yield [['game_version' => 666]];
        yield [['device_model' => 666]];
        yield [['device_os' => 666]];
        yield [['ad_id' => 666]];
        yield [['sdk' => 666]];
        yield [['tags' => 666]];
    }

    /**
     * @dataProvider wrongValueTypesProvider
     */
    public function testResolveWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->deviceSessionResolver->resolve($wrongOption);
    }

    public function testResolveWithWrongOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->deviceSessionResolver->resolve(['wrongOption' => 'wrongValue']);
    }
}
