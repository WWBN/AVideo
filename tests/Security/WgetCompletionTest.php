<?php

namespace Tests\Security\WgetCompletion;

use PHPUnit\Framework\TestCase;

// Select the production Linux command branch without running a real external download.
const PHP_OS = 'Linux';
$wgetSource = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsExec.php');
$wgetStart = strpos($wgetSource, 'function wget(');
$wgetEnd = strpos($wgetSource, 'function getDirSize(', $wgetStart);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($wgetSource, $wgetStart, $wgetEnd - $wgetStart));
unset($wgetSource, $wgetStart, $wgetEnd);

class Download {
    public static $exitCode = 0;
    public static $exists = true;
    public static $size = 256;
    public static $locks = 0;
}
function isValidURL($url) { return true; }
function wgetIsLocked($url) { return false; }
function wgetLock($url) { ++Download::$locks; }
function wgetRemoveLock($url) { --Download::$locks; }
function _error_log($message) {}
function exec($command, &$output, &$code) { $output = []; $code = Download::$exitCode; }
function file_exists($path) { return Download::$exists; }
function filesize($path) { return Download::$size; }
function unlink($path) { Download::$exists = false; return true; }

class WgetCompletionTest extends TestCase
{
    protected function setUp(): void {
        Download::$exitCode = 0;
        Download::$exists = true;
        Download::$size = 256;
        Download::$locks = 0;
    }
    public function testLegacyCallerRejectsAndRemovesPartialDownload() {
        Download::$exitCode = 4;
        $this->assertFalse(wget('https://media.test/video.mp4', '/temporary/video.mp4'));
        $this->assertFalse(Download::$exists);
        $this->assertSame(0, Download::$locks);
    }
    public function testLegacyCallerRejectsEmptySuccessfulDownload() {
        Download::$size = 0;
        $this->assertFalse(wget('https://media.test/video.mp4', '/temporary/video.mp4'));
    }
    public function testCompletedLegacyDownloadStillReturnsTrue() {
        $this->assertTrue(wget('https://media.test/video.mp4', '/temporary/video.mp4'));
    }
    public function testLivePlaylistCallerStillAcceptsSuccessfulEmptyResponse() {
        $savedName = $_SERVER['SCRIPT_NAME'] ?? null;
        try {
            $_SERVER['SCRIPT_NAME'] = '/plugin/Live/m3u8.php';
            Download::$size = 0;
            $this->assertTrue(wget('https://media.test/live.m3u8', '/temporary/live.m3u8'));
        } finally {
            if ($savedName === null) { unset($_SERVER['SCRIPT_NAME']); }
            else { $_SERVER['SCRIPT_NAME'] = $savedName; }
        }
    }
}
