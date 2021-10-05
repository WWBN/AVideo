<?php

declare(strict_types=1);

namespace OneSignal\Tests\Resolver;

use OneSignal\Resolver\DeviceTagsResolver;
use OneSignal\Tests\OneSignalTestCase;
use stdClass;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class DeviceTagsResolverTest extends OneSignalTestCase
{
    /**
     * @var DeviceTagsResolver
     */
    private $deviceResolver;

    protected function setUp(): void
    {
        $this->deviceResolver = new DeviceTagsResolver();
    }

    public function testResolveWithValidValues(): void
    {
        $expectedData = [
            'tags' => [
                'a' => '1',
                'foo' => '',
            ],
        ];

        self::assertEquals($expectedData, $this->deviceResolver->resolve($expectedData));
    }

    public function testResolveWithMissingRequiredValue(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->deviceResolver->resolve([]);
    }

    public function wrongValueTypesProvider(): iterable
    {
        yield [['tags' => 777]];
        yield [['tags' => 'string']];
        yield [['tags' => true]];
        yield [['tags' => new stdClass()]];
    }

    /**
     * @dataProvider wrongValueTypesProvider
     */
    public function testResolveWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->deviceResolver->resolve($wrongOption);
    }

    public function testResolveWithWrongOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->deviceResolver->resolve(['device_tags' => 'wrongValue']);
    }
}
