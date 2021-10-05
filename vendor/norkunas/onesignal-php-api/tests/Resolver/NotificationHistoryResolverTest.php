<?php

declare(strict_types=1);

namespace OneSignal\Tests\Resolver;

use OneSignal\Resolver\NotificationHistoryResolver;
use OneSignal\Tests\OneSignalTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class NotificationHistoryResolverTest extends OneSignalTestCase
{
    /**
     * @var NotificationHistoryResolver
     */
    private $notificationHistoryResolver;

    protected function setUp(): void
    {
        $this->notificationHistoryResolver = new NotificationHistoryResolver($this->createConfig());
    }

    public function testResolveWithValidValues(): void
    {
        $expectedData = [
            'events' => 'sent',
            'email' => 'example@example.com',
            'app_id' => 'fakeApplicationId',
        ];

        self::assertEquals($expectedData, $this->notificationHistoryResolver->resolve($expectedData));
    }

    public function testResolveWithMissingRequiredValue(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->notificationHistoryResolver->resolve([]);
    }

    public function wrongValueTypesProvider(): iterable
    {
        yield [['events' => 666, 'email' => 'example@example.com']];
        yield [['events' => 'sent', 'email' => 666]];
    }

    /**
     * @dataProvider wrongValueTypesProvider
     */
    public function testResolveWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->notificationHistoryResolver->resolve($wrongOption);
    }

    public function testResolveWithWrongOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->notificationHistoryResolver->resolve(['events' => 'sent', 'wrongOption' => 'wrongValue']);
    }
}
