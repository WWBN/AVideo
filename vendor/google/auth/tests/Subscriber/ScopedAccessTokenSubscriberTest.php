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

namespace Google\Auth\Tests\Subscriber;

use Google\Auth\Subscriber\ScopedAccessTokenSubscriber;
use Google\Auth\Tests\BaseTest;
use GuzzleHttp\Client;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Transaction;
use Prophecy\Argument;

class ScopedAccessTokenSubscriberTest extends BaseTest
{
    const TEST_SCOPE = 'https://www.googleapis.com/auth/cloud-taskqueue';

    private $mockCacheItem;
    private $mockCache;
    private $mockRequest;

    protected function setUp()
    {
        $this->onlyGuzzle5();

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
        new ScopedAccessTokenSubscriber($fakeAuthFunc, new \stdClass(), array());
    }

    public function testSubscribesToEvents()
    {
        $fakeAuthFunc = function ($unused_scopes) {
            return '1/abcdef1234567890';
        };
        $s = new ScopedAccessTokenSubscriber($fakeAuthFunc, self::TEST_SCOPE, array());
        $this->assertArrayHasKey('before', $s->getEvents());
    }

    public function testAddsTheTokenAsAnAuthorizationHeader()
    {
        $fakeAuthFunc = function ($unused_scopes) {
            return '1/abcdef1234567890';
        };
        $s = new ScopedAccessTokenSubscriber($fakeAuthFunc, self::TEST_SCOPE, array());
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'scoped']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $s->onBefore($before);
        $this->assertSame(
            'Bearer 1/abcdef1234567890',
            $request->getHeader('authorization')
        );
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

        // Run the test
        $s = new ScopedAccessTokenSubscriber(
            $fakeAuthFunc,
            self::TEST_SCOPE,
            [],
            $this->mockCache->reveal()
        );
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'scoped']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $s->onBefore($before);
        $this->assertSame(
            'Bearer 2/abcdef1234567890',
            $request->getHeader('authorization')
        );
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

        // Run the test
        $s = new ScopedAccessTokenSubscriber(
            $fakeAuthFunc,
            self::TEST_SCOPE,
            ['prefix' => $prefix],
            $this->mockCache->reveal()
        );
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'scoped']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $s->onBefore($before);
        $this->assertSame(
            'Bearer 2/abcdef1234567890',
            $request->getHeader('authorization')
        );
    }

    public function testShouldSaveValueInCache()
    {
        $token = '2/abcdef1234567890';
        $fakeAuthFunc = function ($unused_scopes) {
            return '2/abcdef1234567890';
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
            ->shouldBeCalledTimes(1);

        $s = new ScopedAccessTokenSubscriber(
            $fakeAuthFunc,
            self::TEST_SCOPE,
            [],
            $this->mockCache->reveal()
        );
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'scoped']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $s->onBefore($before);
        $this->assertSame(
            'Bearer 2/abcdef1234567890',
            $request->getHeader('authorization')
        );
    }

    public function testShouldSaveValueInCacheWithCacheOptions()
    {
        $token = '2/abcdef1234567890';
        $prefix = 'test_prefix_';
        $lifetime = '70707';
        $fakeAuthFunc = function ($unused_scopes) {
            return '2/abcdef1234567890';
        };
        $this->mockCacheItem->isHit()
            ->shouldBeCalledTimes(1)
            ->willReturn(false);
        $this->mockCacheItem->set($token)
            ->shouldBeCalledTimes(1);
        $this->mockCacheItem->expiresAfter($lifetime)
            ->shouldBeCalledTimes(1);
        $this->mockCache->getItem($prefix . $this->getValidKeyName(self::TEST_SCOPE))
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockCache->save(Argument::type('Psr\Cache\CacheItemInterface'))
            ->shouldBeCalledTimes(1);

        // Run the test
        $s = new ScopedAccessTokenSubscriber(
            $fakeAuthFunc,
            self::TEST_SCOPE,
            ['prefix' => $prefix, 'lifetime' => $lifetime],
            $this->mockCache->reveal()
        );
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'scoped']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $s->onBefore($before);
        $this->assertSame(
            'Bearer 2/abcdef1234567890',
            $request->getHeader('authorization')
        );
    }

    public function testOnlyTouchesWhenAuthConfigScoped()
    {
        $fakeAuthFunc = function ($unused_scopes) {
            return '1/abcdef1234567890';
        };
        $s = new ScopedAccessTokenSubscriber($fakeAuthFunc, self::TEST_SCOPE, []);
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'notscoped']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $s->onBefore($before);
        $this->assertSame('', $request->getHeader('authorization'));
    }
}
