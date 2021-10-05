<?php

namespace React\Tests\Cache;

use React\Cache\ArrayCache;

class ArrayCacheTest extends TestCase
{
    /**
     * @var ArrayCache
     */
    private $cache;

    /**
     * @before
     */
    public function setUpArrayCache()
    {
        $this->cache = new ArrayCache();
    }

    /** @test */
    public function getShouldResolvePromiseWithNullForNonExistentKey()
    {
        $success = $this->createCallableMock();
        $success
            ->expects($this->once())
            ->method('__invoke')
            ->with(null);

        $this->cache
            ->get('foo')
            ->then(
                $success,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function setShouldSetKey()
    {
        $setPromise = $this->cache
            ->set('foo', 'bar');

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $setPromise->then($mock);

        $success = $this->createCallableMock();
        $success
            ->expects($this->once())
            ->method('__invoke')
            ->with('bar');

        $this->cache
            ->get('foo')
            ->then($success);
    }

    /** @test */
    public function deleteShouldDeleteKey()
    {
        $this->cache
            ->set('foo', 'bar');

        $deletePromise = $this->cache
            ->delete('foo');

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $deletePromise->then($mock);

        $this->cache
            ->get('foo')
            ->then(
                $this->expectCallableOnce(),
                $this->expectCallableNever()
            );
    }

    public function testGetWillResolveWithNullForCacheMiss()
    {
        $this->cache = new ArrayCache();

        $this->cache->get('foo')->then($this->expectCallableOnceWith(null));
    }

    public function testGetWillResolveWithDefaultValueForCacheMiss()
    {
        $this->cache = new ArrayCache();

        $this->cache->get('foo', 'bar')->then($this->expectCallableOnceWith('bar'));
    }

    public function testGetWillResolveWithExplicitNullValueForCacheHit()
    {
        $this->cache = new ArrayCache();

        $this->cache->set('foo', null);
        $this->cache->get('foo', 'bar')->then($this->expectCallableOnceWith(null));
    }

    public function testLimitSizeToZeroDoesNotStoreAnyData()
    {
        $this->cache = new ArrayCache(0);

        $this->cache->set('foo', 'bar');

        $this->cache->get('foo')->then($this->expectCallableOnceWith(null));
    }

    public function testLimitSizeToOneWillOnlyReturnLastWrite()
    {
        $this->cache = new ArrayCache(1);

        $this->cache->set('foo', '1');
        $this->cache->set('bar', '2');

        $this->cache->get('foo')->then($this->expectCallableOnceWith(null));
        $this->cache->get('bar')->then($this->expectCallableOnceWith('2'));
    }

    public function testOverwriteWithLimitedSizeWillUpdateLRUInfo()
    {
        $this->cache = new ArrayCache(2);

        $this->cache->set('foo', '1');
        $this->cache->set('bar', '2');
        $this->cache->set('foo', '3');
        $this->cache->set('baz', '4');

        $this->cache->get('foo')->then($this->expectCallableOnceWith('3'));
        $this->cache->get('bar')->then($this->expectCallableOnceWith(null));
        $this->cache->get('baz')->then($this->expectCallableOnceWith('4'));
    }

    public function testGetWithLimitedSizeWillUpdateLRUInfo()
    {
        $this->cache = new ArrayCache(2);

        $this->cache->set('foo', '1');
        $this->cache->set('bar', '2');
        $this->cache->get('foo')->then($this->expectCallableOnceWith('1'));
        $this->cache->set('baz', '3');

        $this->cache->get('foo')->then($this->expectCallableOnceWith('1'));
        $this->cache->get('bar')->then($this->expectCallableOnceWith(null));
        $this->cache->get('baz')->then($this->expectCallableOnceWith('3'));
    }

    public function testGetWillResolveWithValueIfItemIsNotExpired()
    {
        $this->cache = new ArrayCache();

        $this->cache->set('foo', '1', 10);

        $this->cache->get('foo')->then($this->expectCallableOnceWith('1'));
    }

    public function testGetWillResolveWithDefaultIfItemIsExpired()
    {
        $this->cache = new ArrayCache();

        $this->cache->set('foo', '1', 0);

        $this->cache->get('foo')->then($this->expectCallableOnceWith(null));
    }

    public function testSetWillOverwritOldestItemIfNoEntryIsExpired()
    {
        $this->cache = new ArrayCache(2);

        $this->cache->set('foo', '1', 10);
        $this->cache->set('bar', '2', 20);
        $this->cache->set('baz', '3', 30);

        $this->cache->get('foo')->then($this->expectCallableOnceWith(null));
    }

    public function testSetWillOverwriteExpiredItemIfAnyEntryIsExpired()
    {
        $this->cache = new ArrayCache(2);

        $this->cache->set('foo', '1', 10);
        $this->cache->set('bar', '2', 0);
        $this->cache->set('baz', '3', 30);

        $this->cache->get('foo')->then($this->expectCallableOnceWith('1'));
        $this->cache->get('bar')->then($this->expectCallableOnceWith(null));
    }

    public function testGetMultiple()
    {
        $this->cache = new ArrayCache();
        $this->cache->set('foo', '1');

        $this->cache
            ->getMultiple(array('foo', 'bar'), 'baz')
            ->then($this->expectCallableOnceWith(array('foo' => '1', 'bar' => 'baz')));
    }

    public function testSetMultiple()
    {
        $this->cache = new ArrayCache();
        $this->cache->setMultiple(array('foo' => '1', 'bar' => '2'), 10);

        $this->cache
            ->getMultiple(array('foo', 'bar'))
            ->then($this->expectCallableOnceWith(array('foo' => '1', 'bar' => '2')));
    }

    public function testDeleteMultiple()
    {
        $this->cache = new ArrayCache();
        $this->cache->setMultiple(array('foo' => 1, 'bar' => 2, 'baz' => 3));

        $this->cache
            ->deleteMultiple(array('foo', 'baz'))
            ->then($this->expectCallableOnceWith(true));

        $this->cache
            ->has('foo')
            ->then($this->expectCallableOnceWith(false));

        $this->cache
            ->has('bar')
            ->then($this->expectCallableOnceWith(true));

        $this->cache
            ->has('baz')
            ->then($this->expectCallableOnceWith(false));
    }

    public function testClearShouldClearCache()
    {
        $this->cache = new ArrayCache();
        $this->cache->setMultiple(array('foo' => 1, 'bar' => 2, 'baz' => 3));

        $this->cache->clear();

        $this->cache
            ->has('foo')
            ->then($this->expectCallableOnceWith(false));

        $this->cache
            ->has('bar')
            ->then($this->expectCallableOnceWith(false));

        $this->cache
            ->has('baz')
            ->then($this->expectCallableOnceWith(false));
    }

    public function hasShouldResolvePromiseForExistingKey()
    {
        $this->cache = new ArrayCache();
        $this->cache->set('foo', 'bar');

        $this->cache
            ->has('foo')
            ->then($this->expectCallableOnceWith(true));
    }

    public function hasShouldResolvePromiseForNonExistentKey()
    {
        $this->cache = new ArrayCache();
        $this->cache->set('foo', 'bar');

        $this->cache
            ->has('foo')
            ->then($this->expectCallableOnceWith(false));
    }

    public function testHasWillResolveIfItemIsNotExpired()
    {
        $this->cache = new ArrayCache();
        $this->cache->set('foo', '1', 10);

        $this->cache
            ->has('foo')
            ->then($this->expectCallableOnceWith(true));
    }

    public function testHasWillResolveIfItemIsExpired()
    {
        $this->cache = new ArrayCache();
        $this->cache->set('foo', '1', 0);

        $this->cache
            ->has('foo')
            ->then($this->expectCallableOnceWith(false));
    }

    public function testHasWillResolveForExplicitNullValue()
    {
        $this->cache = new ArrayCache();
        $this->cache->set('foo', null);

        $this->cache
            ->has('foo')
            ->then($this->expectCallableOnceWith(true));
    }

    public function testHasWithLimitedSizeWillUpdateLRUInfo()
    {
        $this->cache = new ArrayCache(2);

        $this->cache->set('foo', 1);
        $this->cache->set('bar', 2);
        $this->cache->has('foo')->then($this->expectCallableOnceWith(true));
        $this->cache->set('baz', 3);

        $this->cache->has('foo')->then($this->expectCallableOnceWith(1));
        $this->cache->has('bar')->then($this->expectCallableOnceWith(false));
        $this->cache->has('baz')->then($this->expectCallableOnceWith(3));
    }
}
