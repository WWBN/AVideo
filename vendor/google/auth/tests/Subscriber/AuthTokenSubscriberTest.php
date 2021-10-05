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

use Google\Auth\FetchAuthTokenCache;
use Google\Auth\Subscriber\AuthTokenSubscriber;
use Google\Auth\Tests\BaseTest;
use GuzzleHttp\Client;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Transaction;
use Prophecy\Argument;

class AuthTokenSubscriberTest extends BaseTest
{
    private $mockFetcher;
    private $mockCacheItem;
    private $mockCache;

    protected function setUp()
    {
        $this->onlyGuzzle5();

        $this->mockFetcher = $this->prophesize('Google\Auth\FetchAuthTokenInterface');
        $this->mockCacheItem = $this->prophesize('Psr\Cache\CacheItemInterface');
        $this->mockCache = $this->prophesize('Psr\Cache\CacheItemPoolInterface');
    }

    public function testSubscribesToEvents()
    {
        $a = new AuthTokenSubscriber($this->mockFetcher->reveal());
        $this->assertArrayHasKey('before', $a->getEvents());
    }

    public function testOnlyTouchesWhenAuthConfigScoped()
    {
        $s = new AuthTokenSubscriber($this->mockFetcher->reveal());
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'not_google_auth']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $s->onBefore($before);
        $this->assertSame($request->getHeader('authorization'), '');
    }

    public function testAddsTheTokenAsAnAuthorizationHeader()
    {
        $authResult = ['access_token' => '1/abcdef1234567890'];
        $this->mockFetcher->fetchAuthToken(Argument::any())
            ->shouldBeCalledTimes(1)
            ->willReturn($authResult);

        // Run the test.
        $a = new AuthTokenSubscriber($this->mockFetcher->reveal());
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'google_auth']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $a->onBefore($before);
        $this->assertSame(
            $request->getHeader('authorization'),
            'Bearer 1/abcdef1234567890'
        );
    }

    public function testDoesNotAddAnAuthorizationHeaderOnNoAccessToken()
    {
        $authResult = ['not_access_token' => '1/abcdef1234567890'];
        $this->mockFetcher->fetchAuthToken(Argument::any())
            ->shouldBeCalledTimes(1)
            ->willReturn($authResult);

        // Run the test.
        $a = new AuthTokenSubscriber($this->mockFetcher->reveal());
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'google_auth']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $a->onBefore($before);
        $this->assertSame($request->getHeader('authorization'), '');
    }

    public function testUsesCachedAuthToken()
    {
        $cacheKey = 'myKey';
        $token = '2/abcdef1234567890';
        $cachedValue = ['access_token' => $token];
        $this->mockCacheItem->isHit()
            ->shouldBeCalledTimes(1)
            ->willReturn(true);
        $this->mockCacheItem->get()
            ->shouldBeCalledTimes(1)
            ->willReturn($cachedValue);
        $this->mockCache->getItem($cacheKey)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockFetcher->fetchAuthToken()
            ->shouldNotBeCalled();
        $this->mockFetcher->getCacheKey()
            ->willReturn($cacheKey);

        // Run the test.
        $cachedFetcher = new FetchAuthTokenCache(
            $this->mockFetcher->reveal(),
            null,
            $this->mockCache->reveal()
        );
        $a = new AuthTokenSubscriber($cachedFetcher);
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'google_auth']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $a->onBefore($before);
        $this->assertSame(
            $request->getHeader('authorization'),
            'Bearer ' . $token
        );
    }

    public function testGetsCachedAuthTokenUsingCachePrefix()
    {
        $prefix = 'test_prefix_';
        $cacheKey = 'myKey';
        $token = '2/abcdef1234567890';
        $cachedValue = ['access_token' => $token];
        $this->mockCacheItem->isHit()
            ->shouldBeCalledTimes(1)
            ->willReturn(true);
        $this->mockCacheItem->get()
            ->shouldBeCalledTimes(1)
            ->willReturn($cachedValue);
        $this->mockCache->getItem($prefix . $cacheKey)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockFetcher->fetchAuthToken()
            ->shouldNotBeCalled();
        $this->mockFetcher->getCacheKey()
            ->willReturn($cacheKey);

        // Run the test
        $cachedFetcher = new FetchAuthTokenCache(
            $this->mockFetcher->reveal(),
            ['prefix' => $prefix],
            $this->mockCache->reveal()
        );
        $a = new AuthTokenSubscriber($cachedFetcher);
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'google_auth']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $a->onBefore($before);
        $this->assertSame(
            $request->getHeader('authorization'),
            'Bearer ' . $token
        );
    }

    public function testShouldSaveValueInCacheWithCacheOptions()
    {
        $prefix = 'test_prefix_';
        $lifetime = '70707';
        $cacheKey = 'myKey';
        $token = '2/abcdef1234567890';
        $cachedValue = ['access_token' => $token];
        $this->mockCacheItem->get()
            ->willReturn(null);
        $this->mockCacheItem->set($cachedValue)
            ->shouldBeCalledTimes(1)
            ->willReturn(false);
        $this->mockCacheItem->isHit()
            ->willReturn(false);
        $this->mockCacheItem->expiresAfter($lifetime)
            ->shouldBeCalledTimes(1);
        $this->mockCache->getItem($prefix . $cacheKey)
            ->shouldBeCalledTimes(2)
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockCache->save(Argument::type('Psr\Cache\CacheItemInterface'))
            ->willReturn(null);
        $this->mockFetcher->getCacheKey()
            ->willReturn($cacheKey);
        $this->mockFetcher->fetchAuthToken(Argument::any())
            ->willReturn($cachedValue);

        // Run the test
        $cachedFetcher = new FetchAuthTokenCache(
            $this->mockFetcher->reveal(),
            ['prefix' => $prefix, 'lifetime' => $lifetime],
            $this->mockCache->reveal()
        );
        $a = new AuthTokenSubscriber($cachedFetcher);
        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'google_auth']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $a->onBefore($before);
        $this->assertSame(
            $request->getHeader('authorization'),
            'Bearer ' . $token
        );
    }

    /**
     * @dataProvider provideShouldNotifyTokenCallback
     */
    public function testShouldNotifyTokenCallback(callable $tokenCallback)
    {
        $prefix = 'test_prefix_';
        $cacheKey = 'myKey';
        $token = '1/abcdef1234567890';
        $cachedValue = ['access_token' => $token];
        $this->mockCacheItem->get()
            ->willReturn(null);
        $this->mockCacheItem->isHit()
            ->willReturn(false);
        $this->mockCacheItem->set($cachedValue)
            ->willReturn(false);
        $this->mockCacheItem->expiresAfter(Argument::any())
            ->willReturn(null);
        $this->mockCache->getItem($prefix . $cacheKey)
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockCache->save(Argument::type('Psr\Cache\CacheItemInterface'))
            ->willReturn(null);
        $this->mockFetcher->getCacheKey()
            ->willReturn($cacheKey);
        $this->mockFetcher->fetchAuthToken(Argument::any())
            ->shouldBeCalledTimes(1)
            ->willReturn($cachedValue);

        SubscriberCallback::$expectedKey = $this->getValidKeyName($prefix . $cacheKey);
        SubscriberCallback::$expectedValue = $token;
        SubscriberCallback::$called = false;

        // Run the test
        $cachedFetcher = new FetchAuthTokenCache(
            $this->mockFetcher->reveal(),
            ['prefix' => $prefix],
            $this->mockCache->reveal()
        );
        $a = new AuthTokenSubscriber(
            $cachedFetcher,
            null,
            $tokenCallback
        );

        $client = new Client();
        $request = $client->createRequest(
            'GET',
            'http://testing.org',
            ['auth' => 'google_auth']
        );
        $before = new BeforeEvent(new Transaction($client, $request));
        $a->onBefore($before);
        $this->assertTrue(SubscriberCallback::$called);
    }

    public function provideShouldNotifyTokenCallback()
    {
        SubscriberCallback::$phpunit = $this;
        $anonymousFunc = function ($key, $value) {
            SubscriberCallback::staticInvoke($key, $value);
        };
        return [
            ['Google\Auth\Tests\Subscriber\SubscriberCallbackFunction'],
            ['Google\Auth\Tests\Subscriber\SubscriberCallback::staticInvoke'],
            [['Google\Auth\Tests\Subscriber\SubscriberCallback', 'staticInvoke']],
            [$anonymousFunc],
            [[new SubscriberCallback(), 'staticInvoke']],
            [[new SubscriberCallback(), 'methodInvoke']],
            [new SubscriberCallback()],
        ];
    }
}

class SubscriberCallback
{
    public static $phpunit;
    public static $expectedKey;
    public static $expectedValue;
    public static $called = false;

    public function __invoke($key, $value)
    {
        self::$phpunit->assertEquals(self::$expectedKey, $key);
        self::$phpunit->assertEquals(self::$expectedValue, $value);
        self::$called = true;
    }

    public function methodInvoke($key, $value)
    {
        return $this($key, $value);
    }

    public static function staticInvoke($key, $value)
    {
        $instance = new self();
        return $instance($key, $value);
    }
}

function SubscriberCallbackFunction($key, $value)
{
    return SubscriberCallback::staticInvoke($key, $value);
}
