<?php

namespace Tests\Unit\EncoderQueueAcceptance;

use PHPUnit\Framework\TestCase;

// Exercise the real method with isolated transport/model collaborators; loading
// the full Video model would bootstrap the production database and plugins.
$source = file_get_contents(dirname(__DIR__, 2) . '/objects/video.php');
$start = strpos($source, 'public static function postToEncoderQueue($postFields)');
$next = strpos($source, 'public function queue(', $start);
$end = strrpos(substr($source, 0, $next), '/**');
eval('namespace ' . __NAMESPACE__ . '; use stdClass; class QueueSender {' . substr($source, $start, $end - $start) . '}');

function curl_init() { return true; }
function curl_setopt_array($handle, $options) {}
function curl_exec($handle) { return Transport::$body; }
function curl_getinfo($handle, $option) { return Transport::$http; }
function curl_errno($handle) { return Transport::$errno; }
function curl_strerror($errno) { return 'Transport failed'; }
function curl_close($handle) {}
function _error_log($message) {}
function __($message) { return $message; }

class Transport {
    public static $body;
    public static $http;
    public static $errno;
}
class Configuration {
    public static function deleteEncoderURLCache() {}
    public function getEncoderURL() { return 'https://encoder.example.test/'; }
}
class Video {
    const STATUS_ENCODING = 'e';
    const STATUS_ENCODING_ERROR = 'x';
    public static $status;
    public static $saves;
    public function __construct($title, $filename, $id, $refresh) {}
    public function getStatus() { return self::$status; }
    public function setStatus($status) { self::$status = $status; }
    public function save() { ++self::$saves; return 71; }
}

class EncoderQueueAcceptanceTest extends TestCase
{
    private $previousConfig;

    protected function setUp(): void
    {
        $this->previousConfig = $GLOBALS['config'] ?? null;
        $GLOBALS['config'] = new Configuration();
        Video::$status = Video::STATUS_ENCODING;
        Video::$saves = 0;
    }

    protected function tearDown(): void
    {
        $GLOBALS['config'] = $this->previousConfig;
    }

    public function responses()
    {
        return [
            'saved ID' => ['123', 200, 0, false],
            'string ID' => ['"123"', 200, 0, false],
            'created ID' => ['123', 201, 0, false],
            'database insert failed' => ['false', 200, 0, true],
            'zero ID' => ['0', 200, 0, true],
            'negative ID' => ['-1', 200, 0, true],
            'boolean is not ID' => ['true', 200, 0, true],
            'empty body' => ['', 200, 0, true],
            'HTML error' => ['<html>Error</html>', 200, 0, true],
            'JSON error' => ['{"error":true,"msg":"Failed"}', 500, 0, true],
            'server error with numeric body' => ['123', 500, 0, true],
            'redirect' => ['123', 302, 0, true],
            'timeout' => [false, 0, 28, true],
        ];
    }

    /** @dataProvider responses */
    public function testQueueResponseMustConfirmSavedTask($body, $http, $errno, $error)
    {
        Transport::$body = $body;
        Transport::$http = $http;
        Transport::$errno = $errno;
        $result = QueueSender::postToEncoderQueue(['videos_id' => 71]);
        $this->assertSame($error, $result->error);
        $this->assertSame($body, $result->response);
        $this->assertSame($error ? 'x' : 'e', Video::$status);
        $this->assertSame($error ? 1 : 0, Video::$saves);
    }

    public function testFailedRequeueDoesNotHideAnAlreadyPublishedVideo()
    {
        Video::$status = 'a';
        Transport::$body = 'false';
        Transport::$http = 200;
        Transport::$errno = 0;
        $this->assertTrue(QueueSender::postToEncoderQueue(['videos_id' => 71])->error);
        $this->assertSame('a', Video::$status);
        $this->assertSame(0, Video::$saves);
    }

    public function testEncoderRejectsFailedInsertBeforeDispatchingWorker()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/.compose/encoder/view/queue.php');
        $start = strpos($source, "if (empty(\$id)) {\n    _error_log('queue: could not save");
        $this->assertNotFalse($start);
        $code = '$id = false; function _error_log($message) {} '
            . 'function execRun() { echo "WORKER_DISPATCHED"; } '
            . 'register_shutdown_function(function () { echo "\nHTTP=" . http_response_code(); });'
            . substr($source, $start);
        $process = proc_open([PHP_BINARY, '-r', $code], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertStringNotContainsString('WORKER_DISPATCHED', $output);
        $this->assertStringContainsString('HTTP=500', $output);
        $this->assertTrue(json_decode(explode("\n", $output)[0], true)['error']);
    }
}
