<?php

namespace Tests\Unit\CategoryCacheRegression;

use PHPUnit\Framework\TestCase;

// Load the real helpers in isolation: the application bootstrap connects to the
// live database. Only filesystem/plugin collaborators below are replaced.
$source = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
foreach ([['function getSearchVar()', 'function isSearch()'], ['function clearCache(', 'function clearAllUsersSessionCache()']] as $bounds) {
    $start = strpos($source, $bounds[0]);
    $end = strpos($source, $bounds[1], $start);
    eval('namespace ' . __NAMESPACE__ . ';' . substr($source, $start, $end - $start));
}
$source = file_get_contents(dirname(__DIR__, 2) . '/objects/Object.php');
$start = strpos($source, 'interface ObjectInterface');
$end = strpos($source, 'abstract class CacheHandler', $start);
eval('namespace ' . __NAMESPACE__ . ';' . substr($source, $start, $end - $start));

function getTmpDir() { return Fixture::$root . '/tmp/'; }
function getVideosDir() { return Fixture::$root . '/videos/'; }
function getDomain() { return 'category.example.test'; }
function getDeviceName($default) { return $default; }
function fixPath($path) { return $path; }
function _error_log($message) {}
function _session_start() {}
function _file_put_contents($file, $value) { return file_put_contents($file, $value); }
function opcache_reset() { return true; }
function make_path($path) {
    $dir = substr($path, -1) === DIRECTORY_SEPARATOR ? $path : dirname($path);
    return is_dir($dir) || mkdir($dir, 0777, true);
}
function rrmdirCommandLine($path, $async = false) { return rrmdir($path); }
function rrmdir($path) {
    if (strpos($path, Fixture::$root . '/') !== 0) {
        throw new \RuntimeException('Refusing to remove a path outside the test fixture');
    }
    if (!is_dir($path)) {
        return true;
    }
    foreach (new \FilesystemIterator($path) as $file) {
        if ($file->isDir() && !$file->isLink()) {
            rrmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    return rmdir($path);
}
class Fixture { public static $root; }
class AVideoPlugin {
    public static function loadPlugin($name) {}
    public static function loadPluginIfEnabled($name) {}
    public static function getDataObjectIfEnabled($name) { return false; }
}
class User { public static function isLogged() { return false; } }

class CategoryCacheRegressionTest extends TestCase
{
    private $savedGlobals;

    protected function setUp(): void
    {
        foreach (['global', '_getCacheDir', '_SESSION', '_REQUEST', '_SERVER'] as $name) {
            $this->savedGlobals[$name] = $GLOBALS[$name] ?? null;
        }
        Fixture::$root = sys_get_temp_dir() . '/avideo-category-regression-' . uniqid();
        mkdir(getVideosDir(), 0777, true);
        $GLOBALS['global'] = ['webSiteRootURL' => 'https://category.example.test/', 'systemRootPath' => Fixture::$root . '/', 'salt' => 'test-only'];
        $GLOBALS['_getCacheDir'] = [];
        $_SESSION = [];
        $_REQUEST = [];
        $_SERVER['HTTPS'] = 'on';
    }

    protected function tearDown(): void
    {
        foreach (new \FilesystemIterator(Fixture::$root) as $file) {
            rrmdir($file->getPathname());
        }
        rmdir(Fixture::$root);
        foreach ($this->savedGlobals as $name => $value) {
            if ($value === null) {
                unset($GLOBALS[$name]);
            } else {
                $GLOBALS[$name] = $value;
            }
        }
    }

    /** @dataProvider searchRequests */
    public function testSearchAcceptsDataTablesAndLegacyRequests($request, $expected): void
    {
        $_REQUEST = $request;
        $this->assertSame($expected, getSearchVar());
    }

    public function searchRequests(): array
    {
        return [
            [[], ''],
            [['search' => 'CATEGORIA'], 'categoria'],
            [['q' => 'CATEGORIA'], 'categoria'],
            [['searchPhrase' => 'CATEGORIA'], 'categoria'],
            [['search' => ['value' => '', 'regex' => 'false']], ''],
            [['search' => ['value' => 'CATEGORIA', 'regex' => 'false']], 'categoria'],
            [['search' => ['value' => '0', 'regex' => 'false']], '0'],
            [['search' => ['value' => ['invalid']]], ''],
            [['search' => [], 'q' => 'FALLBACK'], 'fallback'],
            [['search' => 'FIRST', 'q' => 'SECOND', 'searchPhrase' => 'THIRD'], 'first'],
            [['q' => ['invalid']], ''],
        ];
    }

    private function writeCache($name): string
    {
        $file = ObjectYPT::getCacheFileName($name, true, true, true);
        file_put_contents($file, '[{"id":24,"name":"deleted category"}]');
        return $file;
    }

    /** @dataProvider fullClearMethods */
    public function testFullClearRemovesCategoryFilesAndPreservesOtherSites($method): void
    {
        $category = $this->writeCache('category/0/list.cache');
        $otherSite = getTmpDir() . 'YPTObjectCache/other-site/list.cache';
        make_path($otherSite);
        file_put_contents($otherSite, 'keep');
        if ($method === 'object') {
            $this->assertTrue(ObjectYPT::deleteALLCache());
            $this->assertFileExists(ObjectYPT::getTmpCacheDir() . 'lastDeleteALLCacheTime.cache');
        } else {
            $this->assertTrue(clearCache());
        }
        $this->assertFileDoesNotExist($category);
        $this->assertSame('keep', file_get_contents($otherSite));
        // A write in the same request must recreate memoized directories.
        $this->assertFileExists($this->writeCache('category/0/list.cache'));
    }

    public function fullClearMethods(): array
    {
        return [['object'], ['admin']];
    }

    public function testFirstPageOnlyClearPreservesCategoryFiles(): void
    {
        $category = $this->writeCache('category/0/list.cache');
        $firstPage = $this->writeCache('firstPage/home');
        $this->assertTrue(clearCache(true));
        $this->assertFileDoesNotExist($firstPage);
        $this->assertFileExists($category);
    }
}
