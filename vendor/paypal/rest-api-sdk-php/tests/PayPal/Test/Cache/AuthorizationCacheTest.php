<?php

namespace PayPal\Test\Cache;

use PayPal\Cache\AuthorizationCache;

/**
 * Test class for AuthorizationCacheTest.
 *
 */
class AuthorizationCacheTest extends \PHPUnit_Framework_TestCase
{
    const CACHE_FILE = 'tests/var/test.cache';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public static function EnabledProvider()
    {
        return array(
            array(array('cache.enabled' => 'true'), true),
            array(array('cache.enabled' => true), true),
        );
    }

    public static function CachePathProvider()
    {
        return array(
            array(array('cache.FileName' => 'temp.cache'), 'temp.cache')
        );
    }

    /**
     *
     * @dataProvider EnabledProvider
     */
    public function testIsEnabled($config, $expected)
    {
        $result = AuthorizationCache::isEnabled($config);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider CachePathProvider
     */
    public function testCachePath($config, $expected)
    {
        $result = AuthorizationCache::cachePath($config);
        $this->assertContains($expected, $result);
    }

    public function testCacheDisabled()
    {
        // 'cache.enabled' => true,
        AuthorizationCache::push(array('cache.enabled' => false), 'clientId', 'accessToken', 'tokenCreateTime', 'tokenExpiresIn');
        AuthorizationCache::pull(array('cache.enabled' => false), 'clientId');
    }

    public function testCachePush()
    {
        AuthorizationCache::push(array('cache.enabled' => true, 'cache.FileName' => AuthorizationCacheTest::CACHE_FILE), 'clientId', 'accessToken', 'tokenCreateTime', 'tokenExpiresIn');
        $contents = file_get_contents(AuthorizationCacheTest::CACHE_FILE);
        $tokens = json_decode($contents, true);
        $this->assertNotNull($contents);
        $this->assertEquals('clientId', $tokens['clientId']['clientId']);
        $this->assertEquals('accessToken', $tokens['clientId']['accessTokenEncrypted']);
        $this->assertEquals('tokenCreateTime', $tokens['clientId']['tokenCreateTime']);
        $this->assertEquals('tokenExpiresIn', $tokens['clientId']['tokenExpiresIn']);

    }

    public function testCachePullNonExisting()
    {
        $result = AuthorizationCache::pull(array('cache.enabled' => true, 'cache.FileName' => AuthorizationCacheTest::CACHE_FILE), 'clientIdUndefined');
        $this->assertNull($result);
    }

    /**
     * @depends testCachePush
     */
    public function testCachePull()
    {
        $result = AuthorizationCache::pull(array('cache.enabled' => true, 'cache.FileName' => AuthorizationCacheTest::CACHE_FILE), 'clientId');
        $this->assertNotNull($result);
        $this->assertTrue(is_array($result));
        $this->assertEquals('clientId', $result['clientId']);
        $this->assertEquals('accessToken', $result['accessTokenEncrypted']);
        $this->assertEquals('tokenCreateTime', $result['tokenCreateTime']);
        $this->assertEquals('tokenExpiresIn', $result['tokenExpiresIn']);

        unlink(AuthorizationCacheTest::CACHE_FILE);
    }

}
