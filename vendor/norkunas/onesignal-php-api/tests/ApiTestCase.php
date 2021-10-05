<?php

declare(strict_types=1);

namespace OneSignal\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use OneSignal\Config;
use OneSignal\OneSignal;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class ApiTestCase extends OneSignalTestCase
{
    /**
     * @param callable|callable[]|ResponseInterface|ResponseInterface[]|iterable|null $response
     */
    protected function createClientMock($response = null): OneSignal
    {
        $config = new Config('fakeApplicationId', 'fakeApplicationAuthKey', 'fakeUserAuthKey');

        $httpClient = new Psr18Client(new MockHttpClient($response));

        $requestFactory = $streamFactory = new Psr17Factory();

        return new OneSignal($config, $httpClient, $requestFactory, $streamFactory);
    }

    protected function loadFixture(string $fileName): string
    {
        $content = file_get_contents(__DIR__."/Fixtures/$fileName");

        if ($content === false) {
            throw new RuntimeException(sprintf('Cannot read "%s" fixture file.', $fileName));
        }

        return $content;
    }
}
