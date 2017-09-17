<?php

namespace React\Tests\Dns\Resolver;

use React\Dns\Resolver\Factory;
use React\Tests\Dns\TestCase;
use React\Dns\Query\HostsFileExecutor;

class FactoryTest extends TestCase
{
    /** @test */
    public function createShouldCreateResolver()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $factory = new Factory();
        $resolver = $factory->create('8.8.8.8:53', $loop);

        $this->assertInstanceOf('React\Dns\Resolver\Resolver', $resolver);
    }

    /** @test */
    public function createWithoutPortShouldCreateResolverWithDefaultPort()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $factory = new Factory();
        $resolver = $factory->create('8.8.8.8', $loop);

        $this->assertInstanceOf('React\Dns\Resolver\Resolver', $resolver);
        $this->assertSame('8.8.8.8:53', $this->getResolverPrivateMemberValue($resolver, 'nameserver'));
    }

    /** @test */
    public function createCachedShouldCreateResolverWithCachedExecutor()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $factory = new Factory();
        $resolver = $factory->createCached('8.8.8.8:53', $loop);

        $this->assertInstanceOf('React\Dns\Resolver\Resolver', $resolver);
        $executor = $this->getResolverPrivateExecutor($resolver);
        $this->assertInstanceOf('React\Dns\Query\CachedExecutor', $executor);
        $recordCache = $this->getCachedExecutorPrivateMemberValue($executor, 'cache');
        $recordCacheCache = $this->getRecordCachePrivateMemberValue($recordCache, 'cache');
        $this->assertInstanceOf('React\Cache\CacheInterface', $recordCacheCache);
        $this->assertInstanceOf('React\Cache\ArrayCache', $recordCacheCache);
    }

    /** @test */
    public function createCachedShouldCreateResolverWithCachedExecutorWithCustomCache()
    {
        $cache = $this->getMockBuilder('React\Cache\CacheInterface')->getMock();
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $factory = new Factory();
        $resolver = $factory->createCached('8.8.8.8:53', $loop, $cache);

        $this->assertInstanceOf('React\Dns\Resolver\Resolver', $resolver);
        $executor = $this->getResolverPrivateExecutor($resolver);
        $this->assertInstanceOf('React\Dns\Query\CachedExecutor', $executor);
        $recordCache = $this->getCachedExecutorPrivateMemberValue($executor, 'cache');
        $recordCacheCache = $this->getRecordCachePrivateMemberValue($recordCache, 'cache');
        $this->assertInstanceOf('React\Cache\CacheInterface', $recordCacheCache);
        $this->assertSame($cache, $recordCacheCache);
    }

    /**
     * @test
     * @dataProvider factoryShouldAddDefaultPortProvider
     */
    public function factoryShouldAddDefaultPort($input, $expected)
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $factory = new Factory();
        $resolver = $factory->create($input, $loop);

        $this->assertInstanceOf('React\Dns\Resolver\Resolver', $resolver);
        $this->assertSame($expected, $this->getResolverPrivateMemberValue($resolver, 'nameserver'));
    }

    public static function factoryShouldAddDefaultPortProvider()
    {
        return array(
            array('8.8.8.8',        '8.8.8.8:53'),
            array('1.2.3.4:5',      '1.2.3.4:5'),
            array('localhost',      'localhost:53'),
            array('localhost:1234', 'localhost:1234'),
            array('::1',            '[::1]:53'),
            array('[::1]:53',       '[::1]:53')
        );
    }

    private function getResolverPrivateExecutor($resolver)
    {
        $executor = $this->getResolverPrivateMemberValue($resolver, 'executor');

        // extract underlying executor that may be wrapped in multiple layers of hosts file executors
        while ($executor instanceof HostsFileExecutor) {
            $reflector = new \ReflectionProperty('React\Dns\Query\HostsFileExecutor', 'fallback');
            $reflector->setAccessible(true);

            $executor = $reflector->getValue($executor);
        }

        return $executor;
    }

    private function getResolverPrivateMemberValue($resolver, $field)
    {
        $reflector = new \ReflectionProperty('React\Dns\Resolver\Resolver', $field);
        $reflector->setAccessible(true);
        return $reflector->getValue($resolver);
    }

    private function getCachedExecutorPrivateMemberValue($resolver, $field)
    {
        $reflector = new \ReflectionProperty('React\Dns\Query\CachedExecutor', $field);
        $reflector->setAccessible(true);
        return $reflector->getValue($resolver);
    }

    private function getRecordCachePrivateMemberValue($resolver, $field)
    {
        $reflector = new \ReflectionProperty('React\Dns\Query\RecordCache', $field);
        $reflector->setAccessible(true);
        return $reflector->getValue($resolver);
    }
}
