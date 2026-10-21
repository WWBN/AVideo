<?php

namespace Tests\Unit\CacheGrowthRegression;

use PHPUnit\Framework\TestCase;

// Isolate production classes from configuration.php and its live DB connection.
class PluginAbstract {}
class ObjectYPT {}
class AVideoPlugin { public static function getDataObject($name) { return new \stdClass(); } }
function getCurrentPage($force = false) { return 1; }
function addQueryStringParameter($url, $key, $value) {
    $parts = parse_url($url);
    parse_str($parts['query'] ?? '', $query);
    $query[$key] = $value;
    return $parts['path'] . '?' . http_build_query($query);
}
function removeQueryStringParameter($url, $key) {
    $parts = parse_url($url);
    parse_str($parts['query'] ?? '', $query);
    unset($query[$key]);
    return $parts['path'] . '?' . http_build_query($query);
}

$cacheGrowthSource = file_get_contents(dirname(__DIR__, 2) . '/objects/functionInfiniteScroll.php');
eval('namespace ' . __NAMESPACE__ . ';' . substr($cacheGrowthSource, 5));
$cacheGrowthSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/Cache/Cache.php');
eval('namespace ' . __NAMESPACE__ . ';' . substr($cacheGrowthSource, strpos($cacheGrowthSource, 'class Cache extends')));
$cacheGrowthSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/Cache/Objects/CachesInDB.php');
eval('namespace ' . __NAMESPACE__ . ';' . substr($cacheGrowthSource, strpos($cacheGrowthSource, 'class CachesInDB extends')));

class CacheGrowthRegressionTest extends TestCase
{
    public function testPaginationHrefSurvivesHtmlParsingWithoutCurrencyEntities(): void
    {
        $html = _getPageItem('/page/2?isInfiniteScroll=1&current=2&showOnly=added', 3, 'fixture');
        preg_match('/href="([^"]+)"/', $html, $match);
        $this->assertStringContainsString('&amp;current=', $match[1]);
        $url = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame('3', $query['current']);
        $this->assertSame('1', $query['isInfiniteScroll']);
        $this->assertStringNotContainsString('¤', $url);
        $this->assertSame('/page/3', parse_url($url, PHP_URL_PATH));
    }

    public function testMalformedScrollRequestsSkipOnlyPageCache(): void
    {
        $savedGet = $_GET;
        $savedServer = $_SERVER;
        try {
            $_SERVER['SCRIPT_FILENAME'] = '/site/view/index.php';
            $method = new \ReflectionMethod(Cache::class, 'shouldSkipPageCacheForCurrentRequest');
            $method->setAccessible(true);
            $plugin = new Cache();
            foreach (['1¤t=20¤t=21', ['1'], 'NaN'] as $value) {
                $_GET = ['isInfiniteScroll' => $value];
                $this->assertTrue($method->invoke($plugin));
            }
            $_GET = ['isInfiniteScroll' => '20'];
            $this->assertFalse($method->invoke($plugin));
            $_GET = [];
            $this->assertFalse($method->invoke($plugin));
        } finally {
            $_GET = $savedGet;
            $_SERVER = $savedServer;
        }
    }

    public function testPageRowsUseConfiguredTtlWhileObjectRowsKeepExistingPolicy(): void
    {
        global $cache_pageCacheLifetimes;
        $saved = $cache_pageCacheLifetimes;
        try {
            $cache_pageCacheLifetimes = ['firstPage/test.cache' => 600];
            $method = new \ReflectionMethod(CachesInDB::class, 'prepareCacheItem');
            $method->setAccessible(true);
            $metadata = ['domain' => 'example.test', 'user_location' => 'US', 'loggedType' => 'n', 'ishttps' => 1];
            $now = time();
            $row = $method->invoke(null, 'firstPage/test.cache', 'html', $metadata, 'UTC', $now);
            $this->assertSame($now + 600, strtotime($row['values'][6]));
            $row = $method->invoke(null, 'object', 'data', $metadata, 'UTC', $now);
            $this->assertGreaterThan($now + 86400, strtotime($row['values'][6]));
        } finally {
            $cache_pageCacheLifetimes = $saved;
        }
    }
}
