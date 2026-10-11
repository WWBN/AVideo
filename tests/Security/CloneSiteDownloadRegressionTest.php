<?php

namespace Tests\Security\CloneSiteDownloadRegression;

use PHPUnit\Framework\TestCase;

$cloneSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/CloneSite/cloneClient.json.php');
$cloneStart = strpos($cloneSource, 'if (!wget($sqlURL,');
$cloneEnd = strpos($cloneSource, 'clearstatcache(true, $sqlFile);', $cloneStart);
eval('namespace ' . __NAMESPACE__ . '; function downloadDump() {
    $sqlURL = "https://clone.test/dump.sql";
    $sqlFile = "fixture-only.sql";
    $verifySSL = true;
    ' . substr($cloneSource, $cloneStart, $cloneEnd - $cloneStart) . '
}');
$cloneStart = strpos($cloneSource, '$content2 = url_get_contents(');
$cloneEnd = strpos($cloneSource, ';', $cloneStart);
eval('namespace ' . __NAMESPACE__ . '; function notifyDeleteDump($verifySSL) {
    $url = "https://clone.test/notify?key=fixture-secret";
    ' . substr($cloneSource, $cloneStart, $cloneEnd - $cloneStart + 1) . '
}');
unset($cloneSource, $cloneStart, $cloneEnd);

class Download {
    public static $body = 'complete dump';
    public static $written;
    public static $exists;
    public static $arguments;
}
function wget(...$arguments) { return false; }
function url_get_contents(...$arguments) { Download::$arguments = $arguments; return Download::$body; }
function _file_put_contents($path, $body) { Download::$exists = true; return Download::$written; }
function unlink($path) { Download::$exists = false; return true; }

class CloneSiteDownloadRegressionTest extends TestCase
{
    /** @dataProvider incompleteWrites */
    public function testFallbackRemovesAnIncompleteDump($written)
    {
        Download::$written = $written;
        Download::$exists = false;
        downloadDump();
        $this->assertFalse(Download::$exists, 'A partial dump must not reach the restore');
    }
    public function incompleteWrites() { return [[false], [5]]; }

    public function testFallbackKeepsACompleteDump()
    {
        Download::$written = strlen(Download::$body);
        Download::$exists = false;
        downloadDump();
        $this->assertTrue(Download::$exists);
    }

    /** @dataProvider certificateModes */
    public function testDeleteNotificationKeepsTheConfiguredCertificatePolicy($verifySSL)
    {
        notifyDeleteDump($verifySSL);
        $this->assertSame($verifySSL, Download::$arguments[6] ?? null);
        $this->assertTrue(Download::$arguments[7] ?? false, 'Sensitive notifications must avoid URL-bearing diagnostics');
    }
    public function certificateModes() { return [[true], [false]]; }
}
