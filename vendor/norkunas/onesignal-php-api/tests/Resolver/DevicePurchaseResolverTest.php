<?php

declare(strict_types=1);

namespace OneSignal\Tests\Resolver;

use OneSignal\Resolver\DevicePurchaseResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class DevicePurchaseResolverTest extends TestCase
{
    /**
     * @var DevicePurchaseResolver
     */
    private $devicePurchaseResolver;

    protected function setUp(): void
    {
        $this->devicePurchaseResolver = new DevicePurchaseResolver();
    }

    public function testResolveWithValidValues(): void
    {
        $expectedData = [
            'existing' => false,
            'purchases' => [
                [
                    'sku' => 'fakeSku',
                    'amount' => 34.98,
                    'iso' => 'fakeIso',
                ],
            ],
        ];

        self::assertEquals($expectedData, $this->devicePurchaseResolver->resolve($expectedData));
    }

    public function testResolveWithMissingRequiredValue(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->devicePurchaseResolver->resolve([]);
    }

    public function testResolveWithMissingRequiredPurchaseValue(): void
    {
        $this->expectException(MissingOptionsException::class);

        $wrongData = [
            'existing' => false,
            'purchases' => [
                [],
            ],
        ];

        $this->devicePurchaseResolver->resolve($wrongData);
    }

    public function wrongValueTypesProvider(): iterable
    {
        yield [['existing' => 666]];
        yield [['purchases' => 666]];
        [[
            'purchases' => [[
                'sku' => 666,
                'amount' => 56.4,
                'iso' => 'value',
            ]],
        ]];
        yield [[
            'purchases' => [[
                'sku' => 'value',
                'amount' => 'wrongType',
                'iso' => 'value',
            ]],
        ]];
        yield [[
            'purchases' => [[
                'sku' => 'value',
                'amount' => 56.4,
                'iso' => 666,
            ]],
        ]];
    }

    /**
     * @dataProvider wrongValueTypesProvider
     */
    public function testResolveWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $requiredOptions = [
            'purchases' => [[
                'sku' => 'value',
                'amount' => 56.4,
                'iso' => 'value',
            ]],
        ];

        $this->devicePurchaseResolver->resolve(array_merge($requiredOptions, $wrongOption));
    }

    public function testResolveWithWrongPurchasesValueTypes(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $wrongData = [
            'existing' => true,
            'purchases' => [
                [
                    'sku' => 666,
                    'amount' => 'wrongType',
                    'iso' => 666,
                ],
            ],
        ];

        $this->devicePurchaseResolver->resolve($wrongData);
    }

    public function testResolveWithWrongOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->devicePurchaseResolver->resolve(['wrongOption' => 'wrongValue']);
    }
}
