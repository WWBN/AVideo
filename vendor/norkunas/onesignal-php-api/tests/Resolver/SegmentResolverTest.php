<?php

declare(strict_types=1);

namespace OneSignal\Tests\Resolver;

use OneSignal\Resolver\SegmentResolver;
use OneSignal\Tests\PrivateAccessorTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SegmentResolverTest extends TestCase
{
    use PrivateAccessorTrait;

    /**
     * @var SegmentResolver
     */
    private $segmentResolver;

    protected function setUp(): void
    {
        $this->segmentResolver = new SegmentResolver();
    }

    public function testResolveWithValidValues(): void
    {
        $expectedData = [
            'id' => '52d5a7cb-59fe-4d0c-a0b9-9a39a21475ad',
            'name' => 'Custom Segment',
            'filters' => [],
        ];

        self::assertEquals($expectedData, $this->segmentResolver->resolve($expectedData));
    }

    public function wrongValueTypesProvider(): iterable
    {
        yield [['id' => 666, 'name' => '']];
        yield [['name' => 666]];
        yield [['filters' => 666, 'name' => '']];
    }

    /**
     * @dataProvider wrongValueTypesProvider
     */
    public function testResolveWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->segmentResolver->resolve($wrongOption);
    }

    public function testResolveWithWrongOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->segmentResolver->resolve(['wrongOption' => 'wrongValue']);
    }

    /****** Private functions testing ******/

    public function testNormalizeFilters(): void
    {
        $method = $this->getPrivateMethod(SegmentResolver::class, 'normalizeFilters');

        $inputData = [
            new OptionsResolver(),
            [
                ['wrongField' => 'wrongValue'],
                ['field' => 'session_count', 'relation' => '>', 'value' => '1'],
                ['operator' => 'AND'],
                ['field' => 'tag', 'relation' => '!=', 'key' => 'tag_key', 'value' => '1'],
                ['operator' => 'OR'],
                ['field' => 'last_session', 'relation' => '<', 'value' => '30'],
            ],
        ];

        $expectedData =
            [
                ['field' => 'session_count', 'relation' => '>', 'value' => '1'],
                ['operator' => 'AND'],
                ['field' => 'tag', 'relation' => '!=', 'key' => 'tag_key', 'value' => '1'],
                ['operator' => 'OR'],
                ['field' => 'last_session', 'relation' => '<', 'value' => '30'],
            ];

        self::assertEquals($expectedData, $method->invokeArgs($this->segmentResolver, $inputData));
    }
}
