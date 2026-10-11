<?php

namespace Tests\Security\WgetWindowsCompletion;

use PHPUnit\Framework\TestCase;

$wgetWindowsSource = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsExec.php');
$wgetWindowsStart = strpos($wgetWindowsSource, 'function wget(');
$wgetWindowsEnd = strpos($wgetWindowsSource, 'function getDirSize(', $wgetWindowsStart);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($wgetWindowsSource, $wgetWindowsStart, $wgetWindowsEnd - $wgetWindowsStart));
unset($wgetWindowsSource, $wgetWindowsStart, $wgetWindowsEnd);

class Download {
    public static $written = 110;
    public static $removed = false;
    public static $locks = 0;
}
function isValidURL($url) { return true; }
function wgetIsLocked($url) { return false; }
function wgetLock($url) { ++Download::$locks; }
function wgetRemoveLock($url) { --Download::$locks; }
function file_put_contents($path, $content) { return Download::$written; }
function unlink($path) { Download::$removed = true; return true; }
function _error_log($message) {}

class WgetWindowsCompletionTest extends TestCase
{
    private $server;
    private $pipes;
    protected function tearDown(): void {
        if ($this->server) {
            foreach ($this->pipes as $pipe) { if (is_resource($pipe)) { fclose($pipe); } }
            proc_terminate($this->server);
            proc_close($this->server);
        }
    }
    public function testStrictWindowsDownloadRejectsAndRemovesAShortWrite() {
        if (PHP_OS_FAMILY !== 'Windows') { $this->markTestSkipped('Exercises the Windows stream branch'); }
        $code = <<<'PHP'
$server = stream_socket_server('tcp://127.0.0.1:0');
echo stream_socket_get_name($server, false), "\n";
fflush(STDOUT);
$client = stream_socket_accept($server, 10);
if (!$client) { exit(1); }
while (($line = fgets($client)) !== false && trim($line) !== '') {}
$body = str_repeat('x', 256);
fwrite($client, "HTTP/1.1 200 OK\r\nContent-Length: 256\r\nConnection: close\r\n\r\n" . $body);
fclose($client);
fclose($server);
PHP;
        $this->server = proc_open([PHP_BINARY, '-r', $code], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $this->pipes);
        $this->assertIsResource($this->server);
        $address = trim(fgets($this->pipes[1]));
        $this->assertMatchesRegularExpression('/^127\.0\.0\.1:[0-9]+$/', $address);
        Download::$written = 110;
        Download::$removed = false;
        Download::$locks = 0;
        $this->assertFalse(wget('http://' . $address . '/dump.sql', 'fixture-only.sql', false, false, true));
        $this->assertTrue(Download::$removed);
        $this->assertSame(0, Download::$locks);
    }
}
