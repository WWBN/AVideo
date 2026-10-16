<?php

namespace Tests\Security\ValidM3U8LinkRedirect;

use PHPUnit\Framework\TestCase;

// Isolate the production helper so requests can be observed without network access.
function isValidURL($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function isSSRFSafeURL($url)
{
    return parse_url($url, PHP_URL_HOST) === 'public.example';
}

function get_headers($url, $associative, $context = null)
{
    ValidM3U8LinkRedirectTest::$headerContexts[] = $context === null ? null : stream_context_get_options($context);
    return ['Content-Length' => '100'];
}

function url_get_contents($url, $ctx = '', $timeout = 0)
{
    return "#EXTM3U\n";
}

function _error_log($message, $level = null)
{
}

class ValidM3U8LinkRedirectTest extends TestCase
{
    public static $headerContexts = [];

    public static function setUpBeforeClass(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
        $start = strpos($source, 'function isValidM3U8Link(');
        $end = strpos($source, "\nfunction ", $start + 1);
        eval('namespace ' . __NAMESPACE__ . ';' . substr($source, $start, $end - $start));
    }

    protected function setUp(): void
    {
        self::$headerContexts = [];
    }

    public function testSSRFSafeCheckDoesNotLetGetHeadersFollowRedirects(): void
    {
        $this->assertTrue(isValidM3U8Link('https://public.example/live', true, 3, true));
        $this->assertCount(1, self::$headerContexts);
        $this->assertSame(0, self::$headerContexts[0]['http']['follow_location']);
    }

    public function testUnsafeInitialURLIsNeverRequested(): void
    {
        $this->assertFalse(isValidM3U8Link('http://169.254.169.254/latest', true, 3, true));
        $this->assertSame([], self::$headerContexts);
    }

    public function testTrustedLiveServerCallersKeepTheDefaultContext(): void
    {
        // Callers that do not opt in use the admin-configured (possibly private) live server.
        $this->assertTrue(isValidM3U8Link('http://192.168.1.10/live/key/index.m3u8', true));
        $this->assertSame([null], self::$headerContexts);
    }
}
