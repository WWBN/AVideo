<?php

declare(strict_types=1);

namespace OneSignal\Tests\Resolver;

use OneSignal\Devices;
use OneSignal\Resolver\DeviceResolver;
use OneSignal\Tests\OneSignalTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class DeviceResolverTest extends OneSignalTestCase
{
    /**
     * @var DeviceResolver
     */
    private $deviceResolver;

    protected function setUp(): void
    {
        $this->deviceResolver = new DeviceResolver($this->createConfig(), false);
    }

    public function testResolveWithValidValues(): void
    {
        $expectedData = [
            'identifier' => 'value',
            'language' => 'value',
            'timezone' => 3564,
            'game_version' => 'value',
            'device_model' => 'value',
            'device_os' => 'value',
            'ad_id' => 'value',
            'sdk' => 'value',
            'session_count' => 23,
            'tags' => ['value'],
            'amount_spent' => 34.2,
            'created_at' => 32,
            'playtime' => 56,
            'badge_count' => 12,
            'last_active' => 98,
            'notification_types' => -2,
            'test_type' => 1,
            'long' => 55.1684595,
            'lat' => 22.7624291,
            'country' => 'LT',
            'external_user_id' => 'value',
            'app_id' => 'value',
            'ip' => '127.0.0.1',
        ];

        $this->deviceResolver->setIsNewDevice(false);
        self::assertEquals($expectedData, $this->deviceResolver->resolve($expectedData));

        unset($expectedData['ip']);

        $expectedData += [
            'device_type' => Devices::CHROME_APP,
        ];

        $this->deviceResolver->setIsNewDevice(true);

        self::assertEquals($expectedData, $this->deviceResolver->resolve($expectedData));
    }

    public function testResolveDefaultValues(): void
    {
        $expectedData = [
            'app_id' => 'fakeApplicationId',
        ];

        $this->deviceResolver->setIsNewDevice(false);
        self::assertEquals($expectedData, $this->deviceResolver->resolve([]));

        $inputData = [
            'device_type' => Devices::WINDOWS_PHONE,
        ];

        $this->deviceResolver->setIsNewDevice(true);
        self::assertEquals(array_merge($inputData, $expectedData), $this->deviceResolver->resolve($inputData));
    }

    public function testResolveWithMissingRequiredValue(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->deviceResolver->setIsNewDevice(true);
        $this->deviceResolver->resolve([]);
    }

    public function newDeviceWrongValueTypesProvider(): iterable
    {
        yield [['identifier' => 666]];
        yield [['language' => 666]];
        yield [['timezone' => 'wrongType']];
        yield [['game_version' => 666]];
        yield [['device_model' => 666]];
        yield [['device_os' => 666]];
        yield [['ad_id' => 666]];
        yield [['sdk' => 666]];
        yield [['session_count' => 'wrongType']];
        yield [['tags' => 666]];
        yield [['amount_spent' => 'wrongType']];
        yield [['created_at' => 'wrongType']];
        yield [['playtime' => 'wrongType']];
        yield [['badge_count' => 'wrongType']];
        yield [['last_active' => 'wrongType']];
        yield [['notification_types' => 'wrongType']];
        yield [['test_type' => 'wrongType']];
        yield [['long' => true]];
        yield [['lat' => true]];
        yield [['country' => false]];
        yield [['app_id' => 666]];
        yield [['device_type' => 666]];
        yield [['external_user_id' => 666]];
    }

    /**
     * @dataProvider newDeviceWrongValueTypesProvider
     */
    public function testResolveNewDeviceWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $requiredOptions = [
            'device_type' => Devices::ANDROID,
        ];

        $this->deviceResolver->setIsNewDevice(true);
        $this->deviceResolver->resolve(array_merge($requiredOptions, $wrongOption));
    }

    public function existingDeviceWrongValueTypesProvider(): iterable
    {
        yield [['ip' => 100]];
        yield [['ip' => 'wrongIP']];
        yield [['ip' => '12222237.0.0.1']];
    }

    /**
     * @dataProvider existingDeviceWrongValueTypesProvider
     */
    public function testResolveExistingDeviceWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->deviceResolver->setIsNewDevice(false);
        $this->deviceResolver->resolve($wrongOption);
    }

    public function testResolveWithWrongOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->deviceResolver->resolve(['wrongOption' => 'wrongValue']);
    }
}
