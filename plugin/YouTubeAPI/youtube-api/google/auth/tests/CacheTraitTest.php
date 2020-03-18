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

namespace Google\Auth\Tests;

use Google\Auth\CacheTrait;

class CacheTraitTest extends \PHPUnit_Framework_TestCase
{
    private $mockFetcher;
    private $mockCacheItem;
    private $mockCache;

    public function setUp()
    {
        $this->mockFetcher =
            $this
                ->getMockBuilder('Google\Auth\FetchAuthTokenInterface')
                ->getMock();
        $this->mockCacheItem =
            $this
                ->getMockBuilder('Psr\Cache\CacheItemInterface')
                ->getMock();
        $this->mockCache =
            $this
                ->getMockBuilder('Psr\Cache\CacheItemPoolInterface')
                ->getMock();
    }

    public function testSuccessfullyPullsFromCacheWithoutFetcher()
    {
        $expectedValue = '1234';
        $this->mockCacheItem
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedValue));
        $this->mockCache
            ->expects($this->once())
            ->method('getItem')
            ->will($this->returnValue($this->mockCacheItem));

        $implementation = new CacheTraitImplementation([
            'cache' => $this->mockCache,
        ]);

        $cachedValue = $implementation->gCachedValue();
        $this->assertEquals($expectedValue, $cachedValue);
    }

    public function testSuccessfullyPullsFromCacheWithFetcher()
    {
        $expectedValue = '1234';
        $this->mockCacheItem
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedValue));
        $this->mockCache
            ->expects($this->once())
            ->method('getItem')
            ->will($this->returnValue($this->mockCacheItem));
        $this->mockFetcher
            ->expects($this->once())
            ->method('getCacheKey')
            ->will($this->returnValue('key'));

        $implementation = new CacheTraitImplementation([
            'cache' => $this->mockCache,
            'fetcher' => $this->mockFetcher,
        ]);

        $cachedValue = $implementation->gCachedValue();
        $this->assertEquals($expectedValue, $cachedValue);
    }

    public function testFailsPullFromCacheWithNoCache()
    {
        $implementation = new CacheTraitImplementation();

        $cachedValue = $implementation->gCachedValue();
        $this->assertEquals(null, $cachedValue);
    }

    public function testFailsPullFromCacheWithoutKey()
    {
        $this->mockFetcher
            ->expects($this->once())
            ->method('getCacheKey')
            ->will($this->returnValue(null));

        $implementation = new CacheTraitImplementation([
            'cache' => $this->mockCache,
            'fetcher' => $this->mockFetcher,
        ]);

        $cachedValue = $implementation->gCachedValue();
    }

    public function testSuccessfullySetsToCacheWithoutFetcher()
    {
        $value = '1234';
        $this->mockCacheItem
            ->expects($this->once())
            ->method('set')
            ->with($value);
        $this->mockCache
            ->expects($this->once())
            ->method('getItem')
            ->with($this->equalTo('key'))
            ->will($this->returnValue($this->mockCacheItem));

        $implementation = new CacheTraitImplementation([
            'cache' => $this->mockCache,
        ]);

        $implementation->sCachedValue($value);
    }

    public function testSuccessfullySetsToCacheWithFetcher()
    {
        $value = '1234';
        $this->mockCacheItem
            ->expects($this->once())
            ->method('set')
            ->with($value);
        $this->mockCache
            ->expects($this->once())
            ->method('getItem')
            ->with($this->equalTo('key'))
            ->will($this->returnValue($this->mockCacheItem));
        $this->mockFetcher
            ->expects($this->once())
            ->method('getCacheKey')
            ->will($this->returnValue('key'));

        $implementation = new CacheTraitImplementation([
            'cache' => $this->mockCache,
            'fetcher' => $this->mockFetcher,
        ]);

        $implementation->sCachedValue($value);
    }

    public function testFailsSetToCacheWithNoCache()
    {
        $this->mockFetcher
            ->expects($this->never())
            ->method('getCacheKey');

        $implementation = new CacheTraitImplementation([
            'fetcher' => $this->mockFetcher,
        ]);

        $implementation->sCachedValue('1234');
    }

    public function testFailsSetToCacheWithoutKey()
    {
        $this->mockFetcher
            ->expects($this->once())
            ->method('getCacheKey')
            ->will($this->returnValue(null));

        $implementation = new CacheTraitImplementation([
            'cache' => $this->mockCache,
            'fetcher' => $this->mockFetcher,
        ]);

        $cachedValue = $implementation->sCachedValue('1234');
        $this->assertNull($cachedValue);
    }
}

class CacheTraitImplementation
{
    use CacheTrait;

    private $cache;
    private $fetcher;
    private $cacheConfig;

    public function __construct(array $config = [])
    {
        $this->cache = isset($config['cache']) ? $config['cache'] : null;
        $this->fetcher = isset($config['fetcher']) ? $config['fetcher'] : null;
        $this->cacheConfig = [
            'prefix' => '',
            'lifetime' => 1000,
        ];
    }

    // allows us to keep trait methods private
    public function gCachedValue()
    {
        return $this->getCachedValue();
    }

    public function sCachedValue($v)
    {
        $this->setCachedValue($v);
    }

    private function getCacheKey()
    {
        return 'key';
    }
}
