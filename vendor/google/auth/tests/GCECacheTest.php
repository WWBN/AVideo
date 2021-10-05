<?php
/*
 * Copyright 2020 Google Inc.
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

use Google\Auth\Credentials\GCECredentials;
use Google\Auth\GCECache;
use GuzzleHttp\Psr7;

class GCECacheTest extends BaseTest
{
    private $mockCacheItem;
    private $mockCache;

    protected function setUp()
    {
        $this->mockCacheItem = $this->prophesize('Psr\Cache\CacheItemInterface');
        $this->mockCache = $this->prophesize('Psr\Cache\CacheItemPoolInterface');
    }

    public function testCachedOnGceTrueValue()
    {
        $cachedValue = true;
        $this->mockCacheItem->isHit()
            ->shouldBeCalledTimes(1)
            ->willReturn(true);
        $this->mockCacheItem->get()
            ->shouldBeCalledTimes(1)
            ->willReturn($cachedValue);
        $this->mockCache->getItem(GCECache::GCE_CACHE_KEY)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockCacheItem->reveal());

        // Run the test.
        $gceCache = new GCECache(
            null,
            $this->mockCache->reveal()
        );
        $this->assertTrue($gceCache->onGce());
    }

    public function testCachedOnGceFalseValue()
    {
        $cachedValue = false;
        $this->mockCacheItem->isHit()
            ->shouldBeCalledTimes(1)
            ->willReturn(true);
        $this->mockCacheItem->get()
            ->shouldBeCalledTimes(1)
            ->willReturn($cachedValue);
        $this->mockCache->getItem(GCECache::GCE_CACHE_KEY)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockCacheItem->reveal());

        // Run the test.
        $gceCache = new GCECache(
            null,
            $this->mockCache->reveal()
        );
        $this->assertFalse($gceCache->onGce());
    }

    public function testUncached()
    {
        $gceIsCalled = false;
        $dummyHandler = function ($request) use (&$gceIsCalled) {
            $gceIsCalled = true;
            return new Psr7\Response(200, [GCECredentials::FLAVOR_HEADER => 'Google']);
        };

        $this->mockCacheItem->isHit()
            ->shouldBeCalledTimes(1)
            ->willReturn(false);
        $this->mockCacheItem->set(true)
            ->shouldBeCalledTimes(1);
        $this->mockCacheItem->expiresAfter(1500)
            ->shouldBeCalledTimes(1);
        $this->mockCache->getItem(GCECache::GCE_CACHE_KEY)
            ->shouldBeCalledTimes(2)
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockCache->save($this->mockCacheItem->reveal())
            ->shouldBeCalledTimes(1);

        // Run the test.
        $gceCache = new GCECache(
            null,
            $this->mockCache->reveal()
        );

        $this->assertTrue($gceCache->onGce($dummyHandler));
        $this->assertTrue($gceIsCalled);
    }

    public function testShouldFetchFromCacheWithCacheOptions()
    {
        $prefix = 'test_prefix_';
        $lifetime = '70707';
        $cachedValue = true;

        $this->mockCacheItem->isHit()
            ->willReturn(true);
        $this->mockCacheItem->get()
            ->willReturn($cachedValue);
        $this->mockCache->getItem($prefix . GCECache::GCE_CACHE_KEY)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->mockCacheItem->reveal());

        // Run the test
        $gceCache = new GCECache(
            ['prefix' => $prefix, 'lifetime' => $lifetime],
            $this->mockCache->reveal()
        );
        $this->assertTrue($gceCache->onGce());
    }

    public function testShouldSaveValueInCacheWithCacheOptions()
    {
        $prefix = 'test_prefix_';
        $lifetime = '70707';
        $gceIsCalled = false;
        $dummyHandler = function ($request) use (&$gceIsCalled) {
            $gceIsCalled = true;
            return new Psr7\Response(200, [GCECredentials::FLAVOR_HEADER => 'Google']);
        };
        $this->mockCacheItem->isHit()
            ->willReturn(false);
        $this->mockCacheItem->set(true)
            ->shouldBeCalledTimes(1);
        $this->mockCacheItem->expiresAfter($lifetime)
            ->shouldBeCalledTimes(1);
        $this->mockCache->getItem($prefix . GCECache::GCE_CACHE_KEY)
            ->shouldBeCalledTimes(2)
            ->willReturn($this->mockCacheItem->reveal());
        $this->mockCache->save($this->mockCacheItem->reveal())
            ->shouldBeCalled();

        // Run the test
        $gceCache = new GCECache(
            ['prefix' => $prefix, 'lifetime' => $lifetime],
            $this->mockCache->reveal()
        );
        $onGce = $gceCache->onGce($dummyHandler);
        $this->assertTrue($onGce);
        $this->assertTrue($gceIsCalled);
    }
}
