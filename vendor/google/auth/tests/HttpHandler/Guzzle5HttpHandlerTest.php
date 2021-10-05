<?php
/*
 * Copyright 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Auth\Tests\HttpHandler;

use Composer\Autoload\ClassLoader;
use Exception;
use Google\Auth\HttpHandler\Guzzle5HttpHandler;
use Google\Auth\Tests\BaseTest;
use GuzzleHttp\Message\FutureResponse;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Ring\Future\CompletedFutureValue;
use GuzzleHttp\Stream\Stream;
use Prophecy\Argument;
use Psr\Http\Message\StreamInterface;

/**
 * @group http-handler
 */
class Guzzle5HttpHandlerTest extends BaseTest
{
    private $mockPsr7Request;
    private $mockRequest;
    private $mockClient;
    private $mockFuture;

    public function setUp()
    {
        $this->onlyGuzzle5();

        $uri = $this->prophesize('Psr\Http\Message\UriInterface');
        $body = $this->prophesize('Psr\Http\Message\StreamInterface');

        $this->mockPsr7Request = $this->prophesize('Psr\Http\Message\RequestInterface');
        $this->mockPsr7Request->getMethod()->willReturn('GET');
        $this->mockPsr7Request->getUri()->willReturn($uri->reveal());
        $this->mockPsr7Request->getHeaders()->willReturn([]);
        $this->mockPsr7Request->getBody()->willReturn($body->reveal());

        $this->mockRequest = $this->prophesize('GuzzleHttp\Message\RequestInterface');
        $this->mockClient = $this->prophesize('GuzzleHttp\Client');
        $this->mockFuture = $this->prophesize('GuzzleHttp\Ring\Future\FutureInterface');
    }

    public function testSuccessfullySendsRealRequest()
    {
        $request = new \GuzzleHttp\Psr7\Request('get', 'https://httpbin.org/get');
        $client = new \GuzzleHttp\Client();
        $handler = new Guzzle5HttpHandler($client);
        $response = $handler($request);
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('url', $json);
        $this->assertEquals((string) $request->getUri(), $json['url']);
    }

    public function testSuccessfullySendsMockRequest()
    {
        $response = new Response(
            200,
            [],
            Stream::factory('Body Text')
        );
        $this->mockClient->send(Argument::type('GuzzleHttp\Message\RequestInterface'))
            ->willReturn($response);
        $this->mockClient->createRequest(
            'GET',
            Argument::type('Psr\Http\Message\UriInterface'),
            Argument::type('array')
        )->willReturn($this->mockRequest->reveal());

        $handler = new Guzzle5HttpHandler($this->mockClient->reveal());
        $response = $handler($this->mockPsr7Request->reveal());
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Body Text', (string) $response->getBody());
    }

    public function testAsyncWithoutGuzzlePromiseThrowsException()
    {
        // Pretend the promise library doesn't exist
        foreach (spl_autoload_functions() as $function) {
            if ($function[0] instanceof ClassLoader) {
                $newAutoloader = clone $function[0];
                $newAutoloader->setPsr4('GuzzleHttp\\Promise\\', '/tmp');
                spl_autoload_register($newAutoloadFunc = [$newAutoloader, 'loadClass']);
                spl_autoload_unregister($previousAutoloadFunc = $function);
            }
        }

        $this->mockClient->send(Argument::type('GuzzleHttp\Message\RequestInterface'))
            ->willReturn(new FutureResponse($this->mockFuture->reveal()));
        $this->mockClient->createRequest('GET', Argument::type('Psr\Http\Message\UriInterface'), Argument::allOf(
            Argument::withEntry('headers', []),
            Argument::withEntry('future', true),
            Argument::that(function ($arg) {
                return $arg['body'] instanceof StreamInterface;
            })
        ))->willReturn($this->mockRequest->reveal());

        $handler = new Guzzle5HttpHandler($this->mockClient->reveal());
        $errorThrown = false;
        try {
            $handler->async($this->mockPsr7Request->reveal());
        } catch (Exception $e) {
            $this->assertEquals(
                'Install guzzlehttp/promises to use async with Guzzle 5',
                $e->getMessage()
            );
            $errorThrown = true;
        }

        // Restore autoloader before assertion (in case it fails)
        spl_autoload_register($previousAutoloadFunc);
        spl_autoload_unregister($newAutoloadFunc);

        $this->assertTrue($errorThrown);
    }

    public function testSuccessfullySendsRequestAsync()
    {
        $response = new Response(
            200,
            [],
            Stream::factory('Body Text')
        );
        $this->mockClient->send(Argument::type('GuzzleHttp\Message\RequestInterface'))
            ->willReturn(new FutureResponse(
                new CompletedFutureValue($response)
            ));
        $this->mockClient->createRequest('GET', Argument::type('Psr\Http\Message\UriInterface'), Argument::allOf(
            Argument::withEntry('headers', []),
            Argument::withEntry('future', true),
            Argument::that(function ($arg) {
                return $arg['body'] instanceof StreamInterface;
            })
        ))->willReturn($this->mockRequest->reveal());

        $handler = new Guzzle5HttpHandler($this->mockClient->reveal());
        $promise = $handler->async($this->mockPsr7Request->reveal());
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $promise->wait());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Body Text', (string) $response->getBody());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This is a test rejection message
     */
    public function testPromiseHandlesException()
    {
        $this->mockClient->send(Argument::type('GuzzleHttp\Message\RequestInterface'))
            ->willReturn(new FutureResponse(
                (new CompletedFutureValue(new Response(200)))->then(function () {
                    throw new Exception('This is a test rejection message');
                })
            ));
        $this->mockClient->createRequest('GET', Argument::type('Psr\Http\Message\UriInterface'), Argument::allOf(
            Argument::withEntry('headers', []),
            Argument::withEntry('future', true),
            Argument::that(function ($arg) {
                return $arg['body'] instanceof StreamInterface;
            })
        ))->willReturn($this->mockRequest->reveal());

        $handler = new Guzzle5HttpHandler($this->mockClient->reveal());
        $promise = $handler->async($this->mockPsr7Request->reveal());
        $promise->wait();
    }

    public function testCreateGuzzle5Request()
    {
        $requestHeaders = [
            'header1' => 'value1',
            'header2' => 'value2',
        ];
        $this->mockPsr7Request->getHeaders()
            ->shouldBeCalledTimes(1)
            ->willReturn($requestHeaders);
        $mockBody = $this->prophesize('Psr\Http\Message\StreamInterface');
        $this->mockPsr7Request->getBody()
            ->shouldBeCalledTimes(1)
            ->willReturn($mockBody->reveal());

        $mockGuzzleRequest = $this->prophesize('GuzzleHttp\Message\RequestInterface');
        $this->mockClient->createRequest(
            'GET',
            Argument::type('Psr\Http\Message\UriInterface'),
            [
                'headers' => $requestHeaders + ['header3' => 'value3'],
                'body' => $mockBody->reveal(),
            ]
        )->shouldBeCalledTimes(1)->willReturn(
            $mockGuzzleRequest->reveal()
        );

        $this->mockClient->send(Argument::type('GuzzleHttp\Message\RequestInterface'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->getGuzzle5ResponseMock()->reveal());

        $handler = new Guzzle5HttpHandler($this->mockClient->reveal());
        $handler($this->mockPsr7Request->reveal(), [
            'headers' => [
                'header3' => 'value3'
            ]
        ]);
    }

    private function getGuzzle5ResponseMock()
    {
        $responseMock = $this->prophesize('GuzzleHttp\Message\ResponseInterface');
        $responseMock->getStatusCode()->willReturn(200);
        $responseMock->getHeaders()->willReturn([]);
        $responseMock->getProtocolVersion()->willReturn('');
        $responseMock->getReasonPhrase()->willReturn('');

        $res = $this->prophesize('GuzzleHttp\Stream\StreamInterface');
        $res->__toString()->willReturn('');
        $responseMock->getBody()->willReturn(
            $res->reveal()
        );

        return $responseMock;
    }
}
