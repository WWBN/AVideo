<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/objects/ObjectCacheMaintenance.php';

class ObjectCacheMaintenanceTest extends TestCase
{
    private $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/avideo-cache-maintenance-' . uniqid();
        mkdir($this->root);
    }

    protected function tearDown(): void
    {
        // Every removal is confined to this freshly created fixture.
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $entry) {
            if ($entry->isDir() && !$entry->isLink()) {
                rmdir($entry->getPathname());
            } else {
                unlink($entry->getPathname());
            }
        }
        rmdir($this->root);
        @unlink($this->root . '.maintenance');
    }

    public function testLegacyBaselineAndAdmissionLimits(): void
    {
        file_put_contents($this->root . '/legacy.cache', str_repeat('a', 40));
        $this->assertFalse(\ObjectCacheMaintenance::write($this->root, $this->root . '/new.cache', 'x', 100));
        $this->assertSame(40, \ObjectCacheMaintenance::cleanup($this->root, 3600)['bytes']);
        $this->assertSame(40, \ObjectCacheMaintenance::write($this->root, $this->root . '/new.cache', str_repeat('b', 40), 100));
        $this->assertFalse(\ObjectCacheMaintenance::write($this->root, $this->root . '/overflow.cache', str_repeat('c', 30), 100));
        $this->assertFalse(\ObjectCacheMaintenance::write($this->root, $this->root . '/third.cache', 'x', 100, 2));
        $this->assertFileDoesNotExist($this->root . '/third.cache');
        $this->assertSame(str_repeat('b', 40), file_get_contents($this->root . '/new.cache'));
    }

    public function testCleanupRemovesExpiredFilesAndEmptyDirectoriesOnly(): void
    {
        mkdir($this->root . '/old/nested', 0777, true);
        foreach (['/old/nested/old.cache', '/old/nested/index.html', '/keep.cache', '/video.mp4'] as $name) {
            file_put_contents($this->root . $name, 'payload');
            touch($this->root . $name, time() - 10000);
        }
        touch($this->root . '/keep.cache');
        $result = \ObjectCacheMaintenance::cleanup($this->root, 3600);
        $this->assertSame(2, $result['deleted']);
        $this->assertDirectoryDoesNotExist($this->root . '/old');
        $this->assertFileExists($this->root . '/keep.cache');
        $this->assertFileExists($this->root . '/video.mp4');
        $this->assertSame(14, $result['bytes']);
    }

    public function testRefreshBetweenReadAndExpirationIsPreserved(): void
    {
        \ObjectCacheMaintenance::cleanup($this->root, 60);
        $file = $this->root . '/key.cache';
        \ObjectCacheMaintenance::write($this->root, $file, 'fresh', 100);
        $this->assertFalse(\ObjectCacheMaintenance::deleteExpired($this->root, $file, time() - 60));
        $this->assertSame('fresh', file_get_contents($file));
        touch($file, time() - 120);
        $this->assertTrue(\ObjectCacheMaintenance::deleteExpired($this->root, $file, time() - 60));
    }

    public function testCleanupSchedulingAndLocksDoNotBlockRequests(): void
    {
        $this->assertTrue(\ObjectCacheMaintenance::requestCleanup($this->root, 3600));
        $this->assertFalse(\ObjectCacheMaintenance::requestCleanup($this->root, 3600));
        $handle = fopen($this->root . '.maintenance', 'c+');
        flock($handle, LOCK_EX);
        $this->assertFalse(\ObjectCacheMaintenance::cleanup($this->root, 60));
        $this->assertFalse(\ObjectCacheMaintenance::write($this->root, $this->root . '/key.cache', 'x', 100));
        fclose($handle);
        $this->assertIsArray(\ObjectCacheMaintenance::cleanup($this->root, 60));
    }

    public function testTraversalIsRefused(): void
    {
        \ObjectCacheMaintenance::cleanup($this->root, 60);
        $this->assertFalse(\ObjectCacheMaintenance::write($this->root, $this->root . '/../outside.cache', 'x', 100));
    }

    public function testManyUniqueRequestsStayWithinBothLimits(): void
    {
        \ObjectCacheMaintenance::cleanup($this->root, 60);
        $accepted = 0;
        for ($i = 0; $i < 2000; $i++) {
            if (\ObjectCacheMaintenance::write($this->root, $this->root . '/' . $i . '.cache', str_repeat('x', 256), 16384, 33) !== false) {
                $accepted++;
            }
        }
        $this->assertSame(32, $accepted);
        $result = \ObjectCacheMaintenance::cleanup($this->root, 60);
        $this->assertSame(8192, $result['bytes']);
        $this->assertCount(32, glob($this->root . '/*.cache'));
    }

    public function testConcurrentWritersReadersAndCleaner(): void
    {
        \ObjectCacheMaintenance::cleanup($this->root, 60);
        $sourcePath = var_export(dirname(__DIR__, 2) . '/objects/ObjectCacheMaintenance.php', true);
        $worker = $this->root . '/worker.php';
        file_put_contents($worker, '<?php require ' . $sourcePath . '; function _error_log($m) {} for ($i=0;$i<100;$i++) { ObjectCacheMaintenance::write($argv[1], $argv[1]."/shared.cache", str_repeat($argv[2], 2048), 16384); if ($i%10===0) ObjectCacheMaintenance::cleanup($argv[1],60); usleep(1000); }');
        $processes = [];
        for ($i = 0; $i < 3; $i++) {
            $processes[] = proc_open([PHP_BINARY, $worker, $this->root, chr(65 + $i)], [['pipe', 'r'], ['file', $this->root . '/worker.out', 'a'], ['file', $this->root . '/worker.err', 'a']], $pipes);
            fclose($pipes[0]);
        }
        for ($i = 0; $i < 150; $i++) {
            $data = @file_get_contents($this->root . '/shared.cache');
            if ($data !== false) {
                $this->assertSame(2048, strlen($data));
                $this->assertSame(str_repeat($data[0], 2048), $data);
            }
            usleep(1000);
        }
        foreach ($processes as $process) {
            $this->assertSame(0, proc_close($process));
        }
        $this->assertSame('', file_get_contents($this->root . '/worker.err'));
        $this->assertFileExists($this->root . '/shared.cache');
    }
}
