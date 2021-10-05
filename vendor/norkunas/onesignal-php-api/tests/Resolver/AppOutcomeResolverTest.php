<?php

declare(strict_types=1);

namespace OneSignal\Tests\Resolver;

use OneSignal\Resolver\AppOutcomesResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class AppOutcomeResolverTest extends TestCase
{
    /**
     * @var AppOutcomesResolver
     */
    private $appResolver;

    protected function setUp(): void
    {
        $this->appResolver = new AppOutcomesResolver();
    }

    public function testResolveWithValidValues(): void
    {
        $expectedData = [
            'outcome_names' => [
                'os__session_duration.count',
                'os__click.count',
            ],
            'outcome_time_range' => '1mo',
            'outcome_platforms' => [0, 1, 2],
            'outcome_attribution' => 'direct',
        ];

        self::assertEquals(
            array_merge($expectedData, [
                'outcome_platforms' => '0,1,2',
            ]),
            $this->appResolver->resolve($expectedData)
        );
    }

    public function testResolveWithMissingRequiredValue(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->appResolver->resolve([]);
    }

    public function wrongValueTypesProvider(): iterable
    {
        yield [['outcome_names' => 100]];
        yield [['outcome_names' => [1, 2]]];
        yield [['outcome_time_range' => 1]];
        yield [['outcome_time_range' => '2d']];
        yield [['outcome_platforms' => 0]];
        yield [['outcome_platforms' => ['0']]];
        yield [['outcome_platforms' => [100]]];
        yield [['outcome_attribution' => []]];
        yield [['outcome_attribution' => 'indirect']];
    }

    /**
     * @dataProvider wrongValueTypesProvider
     */
    public function testResolveWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $requiredOptions = [
            'outcome_names' => ['os__click.count'],
        ];

        $this->appResolver->resolve(array_merge($requiredOptions, $wrongOption));
    }

    public function testResolveWithWrongOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->appResolver->resolve(['wrongOption' => 'wrongValue']);
    }
}
