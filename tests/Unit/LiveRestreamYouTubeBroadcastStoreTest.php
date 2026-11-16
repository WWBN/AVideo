<?php

namespace Tests\Unit\YouTubeBroadcastStore;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/objects/ObjectCacheMaintenance.php';
require_once dirname(__DIR__, 2) . '/plugin/Live/standAloneFiles/restreamProfiles.php';

class ObjectYPT
{
    public static $directory;
    public static function getTmpCacheDir() { return self::$directory . DIRECTORY_SEPARATOR; }
    public static function scheduleCacheMaintenance() {}
}

class LiveRestreamYouTubeBroadcastStoreTest extends TestCase
{
    private $savedGlobal;

    protected function setUp(): void
    {
        $this->savedGlobal = $GLOBALS['global'];
        $GLOBALS['global'] = ['objectCacheMaxBytes' => 1024, 'objectCacheMaxFiles' => 4, 'objectCacheMaxAge' => 60];
        ObjectYPT::$directory = sys_get_temp_dir() . '/avideo-youtube-store-' . uniqid();
        mkdir(ObjectYPT::$directory);
        \ObjectCacheMaintenance::cleanup(ObjectYPT::$directory, 60);
        if (!class_exists(__NAMESPACE__ . '\\Live_restreams', false)) {
            $storeSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/Live/Objects/Live_restreams.php');
            $storeSource = substr($storeSource, strpos($storeSource, 'class Live_restreams'));
            $storeSource = str_replace('__DIR__', var_export(dirname(__DIR__, 2) . '/plugin/Live/Objects', true), $storeSource);
            eval('namespace ' . __NAMESPACE__ . '; use \\ObjectCacheMaintenance;' . $storeSource);
        }
    }

    protected function tearDown(): void
    {
        $storeIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ObjectYPT::$directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($storeIterator as $entry) {
            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir(ObjectYPT::$directory);
        @unlink(ObjectYPT::$directory . '.maintenance');
        $GLOBALS['global'] = $this->savedGlobal;
    }

    private function answer()
    {
        return json_encode(['error' => false, 'broadcast_id' => 'oldVideo123', 'stream_key' => 'FAKE_SECRET_KEY']);
    }

    public function testSessionIsolationAndNoStreamKeyOnDisk(): void
    {
        $this->assertTrue(Live_restreams::saveYouTubeBroadcastFromRestreamerResponse(1, 2, $this->answer()));
        $this->assertSame('oldVideo123', Live_restreams::getYouTubeBroadcastIdToReuse(1, 2));
        $this->assertSame('', Live_restreams::getYouTubeBroadcastIdToReuse(1, 3));
        $this->assertSame('', Live_restreams::getYouTubeBroadcastIdToReuse(2, 2));
        $file = ObjectYPT::getTmpCacheDir() . 'LiveRestreamYouTubeBroadcast/1_2.json';
        $this->assertSame(['broadcast_id', 'saved_at'], array_keys(json_decode(file_get_contents($file), true)));
        $this->assertStringNotContainsString('FAKE_SECRET_KEY', file_get_contents($file));
        touch($file, time() - 120);
        clearstatcache();
        $this->assertSame('', Live_restreams::getYouTubeBroadcastIdToReuse(1, 2));
        \ObjectCacheMaintenance::cleanup(ObjectYPT::getTmpCacheDir(), 60);
        $this->assertFileDoesNotExist($file);
    }

    public function testArbitraryHistoryIdsCannotBypassAdmissionLimits(): void
    {
        $accepted = 0;
        for ($history = 1; $history <= 30; $history++) {
            $accepted += intval(Live_restreams::saveYouTubeBroadcastFromRestreamerResponse(1, $history, $this->answer()));
        }
        $this->assertSame(3, $accepted); // Three JSON records plus the directory's index marker.
        $this->assertCount(3, glob(ObjectYPT::getTmpCacheDir() . 'LiveRestreamYouTubeBroadcast/*.json'));
        $GLOBALS['global']['objectCacheMaxBytes'] = 1;
        $this->assertFalse(Live_restreams::saveYouTubeBroadcastFromRestreamerResponse(2, 1, $this->answer()));
    }
}
