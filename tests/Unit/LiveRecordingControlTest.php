<?php

namespace Tests\Unit\LiveRecordingControl;

use PHPUnit\Framework\TestCase;

class ControlState
{
    public static $docker;
    public static $response;
}

function isDocker() { return ControlState::$docker; }
function url_get_contents(...$args) { return ControlState::$response; }
function _error_log(...$args) {}

class LiveRecordingControlTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(__NAMESPACE__ . '\\RecordingControl', false)) {
            $source = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/plugin/Live/Live.php'));
            self::assertSame(1, preg_match('/public static function controlRecording\(.*?\n    }\n/s', $source, $match));
            eval('namespace ' . __NAMESPACE__ . '; use stdClass; class RecordingControl { ' . $match[0]
                . ' public static function getStartRecordURL(...$args) { return "http://live.example.invalid/control/record/start"; }'
                . ' public static function getStopRecordURL(...$args) { return "http://live.example.invalid/control/record/stop"; } }');
        }
    }

    /** @dataProvider controllerResponses */
    public function testControllerResponseMatchesItsTransport($docker, $response, $error, $message)
    {
        ControlState::$docker = $docker;
        ControlState::$response = $response;
        $result = RecordingControl::controlRecording('fixture', 0);
        self::assertSame($error, $result->error);
        self::assertSame($message, $result->msg);
        self::assertSame(!$error, $result->remoteResponse);
    }

    public function controllerResponses(): array
    {
        return [
            'nginx Docker path' => [true, "/HLS/recordings/fixture.flv\n", false, ''],
            'nginx legacy path' => [true, '/var/www/tmp/fixture.flv', false, ''],
            'standalone JSON' => [false, '{"error":false,"msg":"","response":"/var/www/tmp/fixture.flv"}', false, ''],
            'Docker JSON controller' => [true, '{"error":false,"msg":"","response":"/var/www/tmp/fixture.flv"}', false, ''],
            'raw path outside Docker' => [false, '/var/www/tmp/fixture.flv', true, 'JSON response fail'],
            'HTML error' => [true, '<html>404 Not Found</html>', true, 'JSON response fail'],
            'empty response' => [true, '', true, 'Control response fail'],
            'transport failure' => [true, false, true, 'Control response fail'],
        ];
    }
}
