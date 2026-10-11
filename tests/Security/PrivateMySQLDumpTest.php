<?php

namespace Tests\Security\PrivateMySQLDump;

use PHPUnit\Framework\TestCase;

$source = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsMySQL.php');
$start = strpos($source, 'function getPrivateMySQLDumpInstancePrefix()');
$end = strpos($source, "\nfunction dumpMySQLDatabase(", $start);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($source, $start, $end - $start));

class Fixture { public static $tempRoot; public static $storageRoot; }
class Video { public static function getStoragePath() { return Fixture::$storageRoot; } }
function sys_get_temp_dir() { return Fixture::$tempRoot; }
function _uniqid() { return bin2hex(random_bytes(16)); }

class PrivateMySQLDumpTest extends TestCase
{
    private $previousGlobal;
    private $directory;
    private $dump;
    private $otherDump;
    private $otherSiteRoot;
    private $legacyDump;
    protected function setUp(): void {
        $this->previousGlobal = $GLOBALS['global'] ?? null;
        $this->directory = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'avideo-dump-test-' . _uniqid();
        mkdir($this->directory, 0700);
        mkdir($this->directory . '/public', 0700);
        mkdir($this->directory . '/private', 0700);
        $GLOBALS['global'] = ['systemRootPath' => $this->directory . '/public'];
        Fixture::$storageRoot = $this->directory . '/public';
        Fixture::$tempRoot = $this->directory . '/private';
    }
    protected function tearDown(): void {
        if ($this->dump) { unlink($this->dump); rmdir(dirname($this->dump)); }
        if ($this->otherDump) { unlink($this->otherDump); rmdir(dirname($this->otherDump)); }
        if ($this->legacyDump) { unlink($this->legacyDump); }
        if ($this->otherSiteRoot) { rmdir($this->otherSiteRoot . '/videos'); rmdir($this->otherSiteRoot); }
        if (is_dir($this->directory . '/public/videos')) { rmdir($this->directory . '/public/videos'); }
        rmdir($this->directory . '/public');
        rmdir($this->directory . '/private');
        rmdir($this->directory);
        $GLOBALS['global'] = $this->previousGlobal;
    }
    public function testDumpIsCreatedInPrivateDirectoryWithUnpredictableName() {
        $this->dump = createPrivateMySQLDumpPath();
        $this->assertIsString($this->dump);
        $this->assertFileExists($this->dump);
        $this->assertSame(0, strpos(str_replace('\\', '/', $this->dump), str_replace('\\', '/', realpath(Fixture::$tempRoot)) . '/'));
        $this->assertMatchesRegularExpression('/avideo-mysql-[a-f0-9]{64}-[a-f0-9]{32}/', $this->dump);
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->assertSame(0600, fileperms($this->dump) & 0777);
            $this->assertSame(0700, fileperms(dirname($this->dump)) & 0777);
        }
    }
    public function testPublicTempDirectoryFailsClosed() {
        Fixture::$tempRoot = Fixture::$storageRoot;
        $this->assertFalse(createPrivateMySQLDumpPath());
        $this->assertSame(['.', '..'], scandir(Fixture::$tempRoot));
    }
    public function testSeparatePublicStorageRootAlsoFailsClosed() {
        Fixture::$storageRoot = Fixture::$tempRoot;
        $this->assertFalse(createPrivateMySQLDumpPath());
    }
    public function testRestoreFindsPrivateAndLegacyDumpsOnlyForCurrentInstallation() {
        $this->dump = createPrivateMySQLDumpPath();
        $this->otherSiteRoot = $this->directory . '/other-public';
        mkdir($this->otherSiteRoot, 0700);
        mkdir($this->otherSiteRoot . '/videos', 0700);
        $GLOBALS['global']['systemRootPath'] = $this->otherSiteRoot;
        Fixture::$storageRoot = $this->otherSiteRoot;
        $this->otherDump = createPrivateMySQLDumpPath();
        $GLOBALS['global']['systemRootPath'] = $this->directory . '/public';
        Fixture::$storageRoot = $this->directory . '/public';
        mkdir($this->directory . '/public/videos', 0700);
        $this->legacyDump = $this->directory . '/public/videos/mysqldump-legacy.sql';
        file_put_contents($this->legacyDump, 'legacy dump');

        $files = array_map('realpath', getMySQLDumpFilesForRestore());
        $this->assertContains(realpath($this->dump), $files);
        $this->assertContains(realpath($this->legacyDump), $files);
        $this->assertNotContains(realpath($this->otherDump), $files);
    }
}
