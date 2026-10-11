<?php

namespace Tests\Security\VerifiedRemoteFetch;

use PHPUnit\Framework\TestCase;
use ErrorException;

$source = \file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
$start = strpos($source, 'function url_get_contents($url,');
$end = strpos($source, "\nfunction ", $start + 1);
eval('namespace ' . __NAMESPACE__ . '; use ErrorException; ' . substr($source, $start, $end - $start));
$start = strpos($source, 'function ssrfPinnedFetch(');
$end = strpos($source, "\n/**", $start + 1);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($source, $start, $end - $start));

class Transport {
    public static $fopen;
    public static $curl;
    public static $streamURL;
    public static $streamOptions;
    public static $curlOptions;
    public static $responses;
    public static $current;
    public static $logs;
    public static $wgetCalls;
    public static $localBody = false;
}
function isValidURLOrPath($url) { return true; }
function isValidURL($url) { return true; }
function isSSRFSafeURL($url, &$ip = null) { $ip = '203.0.113.10'; return true; }
function isDocker() { return true; }
function try_get_contents_from_local($url) { return Transport::$localBody; }
function getSelfUserAgent() { return 'AVideo test'; }
function isSameDomainAsMyAVideo($url) { return false; }
function ini_get($name) { return Transport::$fopen; }
function function_exists($name) { return $name === 'curl_init' && Transport::$curl; }
function file_get_contents($url, $include, $context) {
    Transport::$streamURL = $url;
    Transport::$streamOptions = stream_context_get_options($context);
    return 'verified body';
}
function remove_utf8_bom($body) { return $body; }
function _error_log($message) { Transport::$logs[] = $message; }
function ssrfResolveRedirectURL($base, $location) { return $location; }
function curl_init() { Transport::$curlOptions[] = []; return count(Transport::$curlOptions) - 1; }
function curl_setopt($handle, $key, $value) { Transport::$curlOptions[$handle][$key] = $value; }
function curl_exec($handle) { Transport::$current = array_shift(Transport::$responses); return Transport::$current['body']; }
function curl_error($handle) { return Transport::$current['error'] ?? ''; }
function curl_getinfo($handle, $key) {
    return $key === CURLINFO_HTTP_CODE ? Transport::$current['status'] : Transport::$current['headerSize'];
}
function curl_close($handle) {}
function wget($url, $file, $debug) { ++Transport::$wgetCalls; return false; }

class VerifiedRemoteFetchTest extends TestCase
{
    private $previousGlobal;
    protected function setUp(): void {
        $this->previousGlobal = $GLOBALS['global'] ?? null;
        $GLOBALS['global'] = ['webSiteRootURL' => 'https://example.test/'];
        Transport::$fopen = true;
        Transport::$curl = true;
        Transport::$curlOptions = [];
        Transport::$logs = [];
        Transport::$wgetCalls = 0;
        Transport::$localBody = false;
    }
    protected function tearDown(): void { $GLOBALS['global'] = $this->previousGlobal; }
    public function testSensitiveFetchValidatesCertificatesAndDoesNotDowngradeDockerURL() {
        $this->assertSame('verified body', url_get_contents('https://example.test/verify?key=test-secret', '', 0, true, false, false, true));
        $this->assertSame('https://example.test/verify?key=test-secret', Transport::$streamURL);
        $this->assertTrue(Transport::$streamOptions['ssl']['verify_peer']);
        $this->assertTrue(Transport::$streamOptions['ssl']['verify_peer_name']);
        $this->assertFalse(Transport::$streamOptions['ssl']['allow_self_signed']);
        $this->assertSame([], Transport::$logs, 'Debug must not expose a sensitive query');
    }
    public function testCallerContextCannotDisableStrictCertificateValidation() {
        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]]);
        url_get_contents('https://remote.test/', $ctx, 0, false, false, false, true);
        $this->assertTrue(Transport::$streamOptions['ssl']['verify_peer']);
        $this->assertTrue(Transport::$streamOptions['ssl']['verify_peer_name']);
    }
    public function testStrictCurlFallbackRejectsCertificateFailure() {
        Transport::$fopen = false;
        Transport::$responses = [['body' => false]];
        $this->assertFalse(url_get_contents('https://remote.test/', '', 0, false, false, false, true));
        $this->assertTrue(Transport::$curlOptions[0][CURLOPT_SSL_VERIFYPEER]);
        $this->assertSame(2, Transport::$curlOptions[0][CURLOPT_SSL_VERIFYHOST]);
        $this->assertSame(0, Transport::$wgetCalls);
    }
    public function testNoVerifiedTransportFailsClosed() {
        Transport::$fopen = Transport::$curl = false;
        $this->assertFalse(url_get_contents('https://remote.test/', '', 0, false, false, false, true));
        $this->assertSame(0, Transport::$wgetCalls);
    }
    public function testPinnedRedirectRetainsCertificateValidation() {
        $headers = "HTTP/1.1 302 Found\r\nLocation: https://other.test/\r\n\r\n";
        Transport::$responses = [
            ['body' => $headers, 'status' => 302, 'headerSize' => strlen($headers)],
            ['body' => 'body', 'status' => 200, 'headerSize' => 0],
        ];
        $this->assertSame('body', ssrfPinnedFetch('https://remote.test/', '203.0.113.10', 0, 5, true));
        $this->assertCount(2, Transport::$curlOptions);
        foreach (Transport::$curlOptions as $options) {
            $this->assertTrue($options[CURLOPT_SSL_VERIFYPEER]);
            $this->assertSame(2, $options[CURLOPT_SSL_VERIFYHOST]);
        }
    }
    public function testPinnedHttpsRedirectCannotDowngradeToHttp() {
        $headers = "HTTP/1.1 302 Found\r\nLocation: http://other.test/\r\n\r\n";
        Transport::$responses = [['body' => $headers, 'status' => 302, 'headerSize' => strlen($headers)]];
        $this->assertFalse(ssrfPinnedFetch('https://remote.test/', '203.0.113.10', 0, 5, true));
        $this->assertCount(1, Transport::$curlOptions);
    }
    public function testStrictDownloadRejectsAnHttpErrorBodyInCurlFallback() {
        Transport::$fopen = false;
        Transport::$responses = [['body' => 'SQL-looking error page', 'status' => 404, 'headerSize' => 0]];
        $this->assertFalse(url_get_contents('https://remote.test/dump.sql', '', 0, false, false, false, false, true));
    }
    public function testStrictDownloadRejectsAnHttpErrorBodyInPinnedFetch() {
        Transport::$responses = [['body' => 'SQL-looking error page', 'status' => 500, 'headerSize' => 0]];
        $this->assertFalse(ssrfPinnedFetch('https://remote.test/dump.sql', '203.0.113.10', 0, 5, false, true));
    }
    public function testStrictDownloadDoesNotAcceptAnUnrelatedLocalFile() {
        Transport::$localBody = 'stale local dump';
        Transport::$fopen = false;
        Transport::$responses = [['body' => false, 'status' => 0, 'headerSize' => 0]];
        $this->assertFalse(url_get_contents('https://remote.test/videos/clones/dump.sql', '', 0, false, false, false, true, true));
    }
    public function testStrictDownloadFailureDoesNotLogTheCloneKey() {
        Transport::$responses = [['body' => false, 'status' => 0, 'headerSize' => 0, 'error' => 'transport failed']];
        $this->assertFalse(ssrfPinnedFetch('https://remote.test/dump.sql?key=clone-secret', '203.0.113.10', 0, 5, false, true));
        $this->assertStringNotContainsString('clone-secret', implode(' ', Transport::$logs));
    }
    public function testStrictDownloadAcceptsASuccessfulCurlResponse() {
        Transport::$fopen = false;
        Transport::$responses = [['body' => 'complete dump', 'status' => 200, 'headerSize' => 0]];
        $this->assertSame('complete dump', url_get_contents('https://remote.test/dump.sql', '', 0, false, false, false, false, true));
    }
    public function testLegacyPrivateIntegrationKeepsItsDefaultContext() {
        url_get_contents('https://private.test/');
        $this->assertFalse(Transport::$streamOptions['ssl']['verify_peer']);
        $this->assertFalse(Transport::$streamOptions['ssl']['verify_peer_name']);
    }
}
