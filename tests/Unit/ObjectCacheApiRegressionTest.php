<?php

namespace Tests\Unit\ObjectCacheApiRegression;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/objects/ObjectCacheMaintenance.php';
$objectCacheApiSource = file_get_contents(dirname(__DIR__, 2) . '/objects/Object.php');
$objectCacheApiStart = strpos($objectCacheApiSource, 'interface ObjectInterface');
$objectCacheApiEnd = strpos($objectCacheApiSource, 'abstract class CacheHandler', $objectCacheApiStart);
$objectCacheApiSource = substr($objectCacheApiSource, $objectCacheApiStart, $objectCacheApiEnd - $objectCacheApiStart);
$objectCacheApiSource = str_replace('__DIR__', var_export(dirname(__DIR__, 2) . '/objects', true), $objectCacheApiSource);
eval('namespace ' . __NAMESPACE__ . '; use \\ObjectCacheMaintenance;' . $objectCacheApiSource);

class Fixture { public static $root; public static $launches = 0; }
class User { public static function isLogged() { return false; } }
class AVideoLog { public static $PERFORMANCE = 0; }
function getTmpDir() { return Fixture::$root . '/tmp/'; }
function getDomain() { return 'example.test'; }
function getDeviceName($default) { return $default; }
function fixPath($path) { return $path; }
function isBot() { return false; }
function isCommandLineInterface() { return false; }
function isSessionStarted() { return false; }
function _json_encode($value) { return json_encode($value); }
function _json_decode($value) { return json_decode($value); }
function url_get_contents($file) { return file_get_contents($file); }
function _error_log($message, $level = null) {}
function _file_put_contents($file, $value) { return file_put_contents($file, $value); }
function getPHP() { return PHP_BINARY; }
function execAsync($command) {
    Fixture::$launches++;
    // Exercise the worker's real cleanup without loading site configuration.
    \ObjectCacheMaintenance::cleanup(ObjectYPT::getTmpCacheDir(), 3600);
}

class ObjectCacheApiRegressionTest extends TestCase
{
    private $saved = [];

    protected function setUp(): void
    {
        foreach (['global', '_getCacheDir', '_getCache', 'getCachesProcessed', '_SESSION', '_GET', '_SERVER'] as $key) {
            $this->saved[$key] = $GLOBALS[$key] ?? null;
        }
        Fixture::$root = sys_get_temp_dir() . '/avideo-cache-api-' . uniqid();
        mkdir(Fixture::$root . '/tmp', 0777, true);
        Fixture::$launches = 0;
        $GLOBALS['global'] = ['systemRootPath' => Fixture::$root . '/', 'webSiteRootURL' => 'https://example.test/', 'salt' => 'test', 'doNotUseCacheDatabase' => true, 'objectCacheMaxAge' => 60];
        $GLOBALS['_getCacheDir'] = [];
        $GLOBALS['_getCache'] = [];
        $GLOBALS['getCachesProcessed'] = [];
        $_SESSION = [];
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(Fixture::$root, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $entry) {
            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir(Fixture::$root);
        foreach ($this->saved as $key => $value) {
            if ($value === null) {
                unset($GLOBALS[$key]);
            } else {
                $GLOBALS[$key] = $value;
            }
        }
    }

    public function testRealApiWritesReadsExpiresAndSchedulesOnce(): void
    {
        $result = ObjectYPT::setCache('firstPage/home', ['id' => 42], true, true);
        $this->assertGreaterThan(0, $result['bytes']);
        $this->assertSame(1, Fixture::$launches);
        $this->assertSame(42, ObjectYPT::getCache('firstPage/home', 60, true, true, true)->id);
        ObjectYPT::setCache('second', 'hello', true, true);
        $this->assertSame(1, Fixture::$launches);
        touch($result['cachefile'], time() - 120);
        $GLOBALS['_getCache'] = [];
        // Even an unlimited read obeys the configured physical retention cap.
        $this->assertNull(ObjectYPT::getCache('firstPage/home', 0, true, true, true));
        $this->assertFileDoesNotExist($result['cachefile']);
    }
}
