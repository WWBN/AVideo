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

use Google\Auth\Middleware\ScopedAccessTokenMiddleware;
use Google\Auth\Tests\BaseTest;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;

class ScopedAccessTokenMiddlewareTest extends BaseTest
{
    const TEST_SCOPE = 'https://www.googleapis.com/auth/cloud-taskqueue';

    private $mockCacheItem;
    private $mockCache;
    private $mockRequest;

    protected function setUp()
    {
        $this->onlyGuzzle6And7();

        $this->mockCacheItem = $this->prophesize('Psr\Cache\CacheItemInterface');
        $this->mockCache = $this->prophesize('Psr\Cache\CacheItemPoolInterface');
        $this->mockRequest = $this->prophesize('GuzzleHttp\Psr7\Request');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequiresScopeAsAStringOrArray()
    {
        $fakeAuthFunc = function ($unused_scopes) {
            return '1/abcdef1234567890';
        };
        new ScopedAccessTokenMiddleware($fakeAuthFunc, new \stdClass());
    }

    public function testAddsTheTokenAsAnAuthorizationHeader()
    {
        $token = '1/abcdef1234567890';
        $fakeAuthFunc = function ($unused_scopes) use ($token) {
            return $token;
        };
        $this->mockRequest->withHeader('authorization', 'Bearer ' . $token)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockRequest->reveal());

        // Run the test
        $middleware = new ScopedAccessTokenMiddleware($fakeAuthFunc, self::TEST_SCOPE);
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['auth' => 'scoped']);
    }

    public function testUsesCachedAuthToken()
    {
        $cachedValue = '2/abcdef1234567890';
        $fakeAuthFunc = function ($unused_scopes) {
            return '';
        };
        $this->mockCacheItem->isHit()
            ->shouldBeCalledTimes(1)
            ->willReturn(true);
        $this->mockCacheItem->get()
            ->shouldBeCalledTimes(1)
            ->willReturn($cachedValue);
        $this->mockCache->getItem($this->getValidKeyName(self::TEST_SCOPE))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockRequest->withHeader('authorization', 'Bearer ' . $cachedValue)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockRequest->reveal());

        // Run the test
        $middleware = new ScopedAccessTokenMiddleware(
            $fakeAuthFunc,
            self::TEST_SCOPE,
            [],
            $this->mockCache->reveal()
        );
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['auth' => 'scoped']);
    }

    public function testGetsCachedAuthTokenUsingCachePrefix()
    {
        $prefix = 'test_prefix_';
        $cachedValue = '2/abcdef1234567890';
        $fakeAuthFunc = function ($unused_scopes) {
            return '';
        };
        $this->mockCacheItem->isHit()
            ->shouldBeCalledTimes(1)
            ->willReturn(true);
        $this->mockCacheItem->get()
            ->shouldBeCalledTimes(1)
            ->willReturn($cachedValue);
        $this->mockCache->getItem($prefix . $this->getValidKeyName(self::TEST_SCOPE))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockRequest->withHeader('authorization', 'Bearer ' . $cachedValue)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockRequest->reveal());

        // Run the test
        $middleware = new ScopedAccessTokenMiddleware(
            $fakeAuthFunc,
            self::TEST_SCOPE,
            ['prefix' => $prefix],
            $this->mockCache->reveal()
        );
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['auth' => 'scoped']);
    }

    public function testShouldSaveValueInCache()
    {
        $token = '2/abcdef1234567890';
        $fakeAuthFunc = function ($unused_scopes) use ($token) {
            return $token;
        };
        $this->mockCacheItem->isHit()
            ->shouldBeCalledTimes(1)
            ->willReturn(false);
        $this->mockCacheItem->set($token)
            ->shouldBeCalledTimes(1)
            ->willReturn(false);
        $this->mockCacheItem->expiresAfter(Argument::any())
            ->shouldBeCalledTimes(1);
        $this->mockCache->getItem($this->getValidKeyName(self::TEST_SCOPE))
            ->shouldBeCalledTimes(2)
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockCache->save(Argument::type('Psr\Cache\CacheItemInterface'))
            ->shouldBeCalled()
            ->willReturn(true);
        $this->mockRequest->withHeader('authorization', 'Bearer ' . $token)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockRequest->reveal());

        // Run the test
        $middleware = new ScopedAccessTokenMiddleware(
            $fakeAuthFunc,
            self::TEST_SCOPE,
            [],
            $this->mockCache->reveal()
        );
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['auth' => 'scoped']);
    }

    public function testShouldSaveValueInCacheWithCacheOptions()
    {
        $token = '2/abcdef1234567890';
        $prefix = 'test_prefix_';
        $lifetime = '70707';
        $fakeAuthFunc = function ($unused_scopes) use ($token) {
            return $token;
        };
        $this->mockCacheItem->isHit()
            ->shouldBeCalledTimes(1)
            ->willReturn(false);
        $this->mockCacheItem->set($token)
            ->shouldBeCalledTimes(1)
            ->willReturn(false);
        $this->mockCacheItem->expiresAfter($lifetime)
            ->shouldBeCalledTimes(1);
        $this->mockCache->getItem($prefix . $this->getValidKeyName(self::TEST_SCOPE))
            ->shouldBeCalledTimes(2)
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockCache->save(Argument::type('Psr\Cache\CacheItemInterface'))
            ->shouldBeCalled()
            ->willReturn(true);
        $this->mockRequest->withHeader('authorization', 'Bearer ' . $token)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockRequest->reveal());

        // Run the test
        $middleware = new ScopedAccessTokenMiddleware(
            $fakeAuthFunc,
            self::TEST_SCOPE,
            ['prefix' => $prefix, 'lifetime' => $lifetime],
            $this->mockCache->reveal()
        );
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['auth' => 'scoped']);
    }

    public function testOnlyTouchesWhenAuthConfigScoped()
    {
        $fakeAuthFunc = function ($unused_scopes) {
            return '1/abcdef1234567890';
        };
        $this->mockRequest->withHeader()->shouldNotBeCalled();

        // Run the test
        $middleware = new ScopedAccessTokenMiddleware($fakeAuthFunc, self::TEST_SCOPE);
        $mock = new MockHandler([new Response(200)]);
        $callable = $middleware($mock);
        $callable($this->mockRequest->reveal(), ['auth' => 'not_scoped']);
    }
}
