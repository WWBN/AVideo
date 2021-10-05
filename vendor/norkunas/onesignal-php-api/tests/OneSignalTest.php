<?php

declare(strict_types=1);

namespace OneSignal\Tests;

use OneSignal\Apps;
use OneSignal\Devices;
use OneSignal\Exception\BadMethodCallException;
use OneSignal\Exception\InvalidArgumentException;
use OneSignal\Exception\JsonException;
use OneSignal\Notifications;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OneSignalTest extends ApiTestCase
{
    public function testSendRequest(): void
    {
        $oneSignal = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame('https://example.com/data.json', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);

            return new MockResponse('{"data":[{"id":1},{"id":2}]}', ['http_code' => 200]);
        });

        $request = $oneSignal->getRequestFactory()->createRequest('GET', 'https://example.com/data.json');
        $request = $request->withHeader('Accept', 'application/json');

        $responseData = $oneSignal->sendRequest($request);

        self::assertSame([
            'data' => [
                ['id' => 1],
                ['id' => 2],
            ],
        ], $responseData);
    }

    public function testSendRequestThrowsIfNotJsonResponse(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage("Response content-type is 'text/html' while a JSON-compatible one was expected.");

        $oneSignal = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame('https://example.com/data.json', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);

            return new MockResponse('<!DOCTYPE html><html><head><title>example</title><body>example</body></head></html>', ['http_code' => 200, 'response_headers' => ['Content-Type' => 'text/html']]);
        });

        $request = $oneSignal->getRequestFactory()->createRequest('GET', 'https://example.com/data.json');
        $request = $request->withHeader('Accept', 'application/json');

        $oneSignal->sendRequest($request);
    }

    public function testSendRequestThrowsIfJsonDecodeFails(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Syntax error');

        $oneSignal = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame('https://example.com/data.json', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);

            return new MockResponse('{"data":[{"id":1},{"id":2}]', ['http_code' => 200]);
        });

        $request = $oneSignal->getRequestFactory()->createRequest('GET', 'https://example.com/data.json');
        $request = $request->withHeader('Accept', 'application/json');

        $oneSignal->sendRequest($request);
    }

    public function testSendRequestThrowsIfDecodedJsonIsNotArray(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('JSON content was expected to decode to an array, string returned.');

        $oneSignal = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame('https://example.com/data.json', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);

            return new MockResponse('"example"', ['http_code' => 200]);
        });

        $request = $oneSignal->getRequestFactory()->createRequest('GET', 'https://example.com/data.json');
        $request = $request->withHeader('Accept', 'application/json');

        $oneSignal->sendRequest($request);
    }

    public function testApiReturnsInstances(): void
    {
        $oneSignal = $this->createClientMock();

        self::assertInstanceOf(Apps::class, $oneSignal->api('apps'));
        self::assertInstanceOf(Devices::class, $oneSignal->api('devices'));
        self::assertInstanceOf(Notifications::class, $oneSignal->api('notifications'));
    }

    public function testApiThrowsForUnknownService(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Undefined api instance called: 'app'.");

        $oneSignal = $this->createClientMock();
        $oneSignal->api('app');
    }

    public function testMagicCallReturnsInstance(): void
    {
        $oneSignal = $this->createClientMock();

        self::assertInstanceOf(Apps::class, $oneSignal->apps());
    }

    public function testMagicCallThrowsWithWrongMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("Undefined method called: 'app'.");

        $oneSignal = $this->createClientMock();
        /* @phpstan-ignore-next-line */
        $oneSignal->app();
    }
}
