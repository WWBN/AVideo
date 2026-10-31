<?php

namespace Tests\Security\EncoderDownloadURLTempFile;

use PHPUnit\Framework\TestCase;

// Runs the real downloadVideoFromDownloadURL() from objects/aVideoEncoder.json.php with the
// network fetch stubbed, to check the name of the temp file it writes (GHSA-p986-p2vg-2pqm).
$encoderSource = file_get_contents(dirname(__DIR__, 2) . '/objects/aVideoEncoder.json.php');
$encoderStart = strpos($encoderSource, 'function downloadVideoFromDownloadURL(');
$encoderEnd = strpos($encoderSource, 'function __errlog(', $encoderStart);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($encoderSource, $encoderStart, $encoderEnd - $encoderStart));
unset($encoderSource, $encoderStart, $encoderEnd);

class Video
{
    public static $storagePath = '';

    public static function getStoragePath()
    {
        return self::$storagePath;
    }
}

class finfo
{
    public function __construct($flags = 0)
    {
    }

    public function file($path)
    {
        return 'video/mp4';
    }
}

class FetchStub
{
    public static $bytes = 30000;
    public static $destinations = [];
}

function isSSRFSafeURL($url, &$resolvedIP = null)
{
    $resolvedIP = null;
    return true;
}

function ssrfPinnedFetchToFile($url, $destPath, $resolvedIP, $timeout)
{
    FetchStub::$destinations[] = $destPath;
    file_put_contents($destPath, str_repeat('0', FetchStub::$bytes));
    return FetchStub::$bytes;
}

function make_path($path)
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

function _uniqid()
{
    return bin2hex(random_bytes(16));
}

function humanFileSize($size)
{
    return (string) $size;
}

function __errlog($txt)
{
}

class EncoderDownloadURLTempFileTest extends TestCase
{
    private $scratchDir;
    private $savedGlobal;
    private $savedFiles;

    protected function setUp(): void
    {
        $this->scratchDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'avideo-encoder-download-' . bin2hex(random_bytes(6)) . DIRECTORY_SEPARATOR;
        Video::$storagePath = $this->scratchDir;
        FetchStub::$bytes = 30000;
        FetchStub::$destinations = [];
        $this->savedGlobal = $GLOBALS['global'] ?? null;
        $this->savedFiles = $_FILES;
        $GLOBALS['global'] = ['allowedExtension' => ['gif', 'jpg', 'mp4', 'webm', 'mp3', 'm4a', 'ogg', 'zip', 'm3u8', 'webp']];
    }

    protected function tearDown(): void
    {
        $GLOBALS['global'] = $this->savedGlobal;
        $_FILES = $this->savedFiles;
        $tmpDir = $this->scratchDir . 'cache' . DIRECTORY_SEPARATOR . 'tmpFile' . DIRECTORY_SEPARATOR;
        if (strpos(realpath($this->scratchDir) ?: '', realpath(sys_get_temp_dir())) === 0) {
            foreach (glob($tmpDir . '*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($tmpDir);
            @rmdir($this->scratchDir . 'cache');
            @rmdir($this->scratchDir);
        }
    }

    private function assertSafeTempName($temp, $extension)
    {
        $this->assertNotFalse($temp);
        $this->assertSame($this->scratchDir . 'cache/tmpFile/', substr($temp, 0, strlen($this->scratchDir . 'cache/tmpFile/')));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}\.' . preg_quote($extension, '/') . '$/', basename($temp));
        $this->assertFileExists($temp);
    }

    public function testQueryStringCannotGiveTheTempFileAPhpName()
    {
        $temp = downloadVideoFromDownloadURL('https://example.com/x.mp4?s=.php');
        $this->assertSafeTempName($temp, 'mp4');
        $this->assertSame([$temp], FetchStub::$destinations);
    }

    public function testFragmentCannotGiveTheTempFileAPhpName()
    {
        $temp = downloadVideoFromDownloadURL('https://example.com/x.mp4#x.php');
        $this->assertSafeTempName($temp, 'mp4');
    }

    public function testDoubleExtensionInThePathIsNotKept()
    {
        $temp = downloadVideoFromDownloadURL('https://example.com/x.php.mp4');
        $this->assertSafeTempName($temp, 'mp4');
    }

    public function testEncoderDownloadURLStillDownloadsWithItsExtension()
    {
        $temp = downloadVideoFromDownloadURL('https://encoder.example.com/videos/avideoTmpFile_12_streamers_id_3_HD.mp4');
        $this->assertSafeTempName($temp, 'mp4');

        $zip = downloadVideoFromDownloadURL('https://encoder.example.com/videos/avideoTmpFile_12_streamers_id_3.zip');
        $this->assertSafeTempName($zip, 'zip');
        $this->assertNotSame($temp, $zip);
    }

    public function testDisallowedExtensionIsRejectedBeforeAnyFetch()
    {
        $this->assertFalse(downloadVideoFromDownloadURL('https://example.com/x.php?y=.mp4'));
        $this->assertSame([], FetchStub::$destinations);
    }

    public function testTooSmallDownloadRemovesItsTempFile()
    {
        FetchStub::$bytes = 100;
        $this->assertFalse(downloadVideoFromDownloadURL('https://example.com/x.mp4'));
        $this->assertCount(1, FetchStub::$destinations);
        $this->assertFileDoesNotExist(FetchStub::$destinations[0]);
    }
}
