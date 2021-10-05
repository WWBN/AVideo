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

namespace Google\Auth\Tests\Middleware;

use Google\Auth\GetQuotaProjectInterface;
use Google\Auth\Middleware\ProxyAuthTokenMiddleware;
use Google\Auth\Tests\BaseTest;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;

class ProxyAuthTokenMiddlewareTest extends BaseTest
{
    private $mockFetcher;
    private $mockRequest;

    protected function setUp()
    {
        $this->onlyGuzzle6And7();

        $this->mockFetcher = $this->prophesize('Google\Auth\FetchAuthTokenInterface');
        $this->mockRequest = $this->prophesize('GuzzleHttp\Psr7\Request');
    }

    public function testOnlyTouchesWhenAuthConfigScoped()
    {
        $this->mockFetcher->fetchAuthToken(Argument::any())
            ->willReturn([]);
        $this->mockRequest->withHeader()->shouldNotBeCalled();

        $middleware = new ProxyAuthTokenMiddleware($this->mockFetcher->reveal());
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['proxy_auth' => 'not_google_auth']);
    }

    public function testAddsTheTokenAsAnAuthorizationHeader()
    {
        $authResult = ['id_token' => '1/abcdef1234567890'];
        $this->mockFetcher->fetchAuthToken(Argument::any())
            ->shouldBeCalledTimes(1)
            ->willReturn($authResult);
        $this->mockRequest->withHeader('proxy-authorization', 'Bearer ' . $authResult['id_token'])
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockRequest->reveal());

        // Run the test.
        $middleware = new ProxyAuthTokenMiddleware($this->mockFetcher->reveal());
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['proxy_auth' => 'google_auth']);
    }

    public function testDoesNotAddAnAuthorizationHeaderOnNoAccessToken()
    {
        $authResult = ['not_access_token' => '1/abcdef1234567890'];
        $this->mockFetcher->fetchAuthToken(Argument::any())
            ->shouldBeCalledTimes(1)
            ->willReturn($authResult);
        $this->mockRequest->withHeader('proxy-authorization', 'Bearer ')
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockRequest->reveal());

        // Run the test.
        $middleware = new ProxyAuthTokenMiddleware($this->mockFetcher->reveal());
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['proxy_auth' => 'google_auth']);
    }

    public function testUsesIdTokenWhenAccessTokenDoesNotExist()
    {
        $token = 'idtoken12345';
        $authResult = ['id_token' => $token];
        $this->mockFetcher->fetchAuthToken(Argument::any())
            ->willReturn($authResult);
        $this->mockRequest->withHeader('proxy-authorization', 'Bearer ' . $token)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockRequest->reveal());

        $middleware = new ProxyAuthTokenMiddleware($this->mockFetcher->reveal());
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['proxy_auth' => 'google_auth']);
    }

    public function testGetQuotaProject()
    {
        $token = 'idtoken12345';
        $authResult = ['id_token' => $token];
        $quotaProject = 'test-quota-project';
        $quotaProjectHeader = GetQuotaProjectInterface::X_GOOG_USER_PROJECT_HEADER;
        $this->mockFetcher->willImplement('Google\Auth\GetQuotaProjectInterface');
        $this->mockFetcher->fetchAuthToken(Argument::any())
            ->willReturn($authResult);
        $this->mockFetcher->getQuotaProject(Argument::any())
            ->willReturn($quotaProject);
        $this->mockRequest->withHeader('proxy-authorization', 'Bearer ' . $token)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockRequest->reveal());
        $this->mockRequest->withHeader($quotaProjectHeader, $quotaProject)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockRequest->reveal());
        $middleware = new ProxyAuthTokenMiddleware($this->mockFetcher->reveal());
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['proxy_auth' => 'google_auth']);
    }
}
