<?php

namespace Tests\Unit\LiveOnPublishRestream;

use Tests\TestCase;

class RestreamBackgroundState
{
    public static $windows = false;
    public static $execAvailable = true;
    public static $php = '/opt/custom php/bin/php';
    public static $commands = [];
    public static $restreams = [];
}

function isWindowsServer()
{
    return RestreamBackgroundState::$windows;
}

function function_exists($name)
{
    return $name === 'exec' ? RestreamBackgroundState::$execAvailable : \function_exists($name);
}

function get_php()
{
    return RestreamBackgroundState::$php;
}

function execAsync($command)
{
    RestreamBackgroundState::$commands[] = $command;
}

/**
 * on_publish used to wait for the restreamer (source check + every destination launched one after
 * the other) before running the plugin hooks, and SendRecordedToEncoder starts the live recording in
 * that hook: the recording began 20 s (two local destinations) to about 47 s (YouTube + Facebook)
 * after the live started, so the beginning of the live was missing from the recorded video.
 */
class LiveOnPublishRestreamInBackgroundTest extends TestCase
{
    private $savedGlobal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedGlobal = $GLOBALS['global'];
        RestreamBackgroundState::$windows = false;
        RestreamBackgroundState::$execAvailable = true;
        RestreamBackgroundState::$php = '/opt/custom php/bin/php';
        RestreamBackgroundState::$commands = [];
        RestreamBackgroundState::$restreams = [];
        if (!class_exists(__NAMESPACE__ . '\\RestreamBackgroundLive', false)) {
            eval('namespace ' . __NAMESPACE__ . '; class RestreamBackgroundLive { public static '
                . $this->methodSource('restreamInBackground')
                . ' public static function restream($id) { RestreamBackgroundState::$restreams[] = $id; return "synchronous"; } }');
        }
        $GLOBALS['global']['systemRootPath'] = '/opt/site with spaces/';
    }

    protected function tearDown(): void
    {
        $GLOBALS['global'] = $this->savedGlobal;
        parent::tearDown();
    }

    private function methodSource($name)
    {
        // Windows checkouts may use CRLF line endings
        $source = str_replace("\r\n", "\n", file_get_contents(\APP_ROOT . '/plugin/Live/Live.php'));
        $this->assertSame(1, preg_match('/function ' . preg_quote($name, '/') . '\(.*?\n    }\n/s', $source, $matches), $name);
        return $matches[0];
    }

    public function testOnPublishDoesNotWaitForTheRestreamerBeforeThePluginHooks()
    {
        $onPublish = $this->methodSource('_on_publish');
        $this->assertStringNotContainsString('self::restream(', $onPublish);
        $this->assertSame(2, substr_count($onPublish, 'self::restreamInBackground($liveTransmitionHistory_id)'));
        $this->assertGreaterThan(
            strrpos($onPublish, 'self::restreamInBackground('),
            strpos($onPublish, 'AVideoPlugin::on_publish(')
        );
    }

    public function testRestreamRunsInItsOwnCliProcessWithEscapedArguments()
    {
        $method = $this->methodSource('restreamInBackground');
        $this->assertStringContainsString("escapeshellarg(\$global['systemRootPath'] . 'plugin/Live/restreamInBackground.php')", $method);
        $this->assertStringContainsString('escapeshellarg((string) $liveTransmitionHistory_id)', $method);
        $this->assertStringContainsString('execAsync($command)', $method);
        // Windows and servers without exec() keep the previous synchronous call
        $this->assertStringContainsString("isWindowsServer() || !function_exists('exec')", $method);
        $this->assertStringContainsString('return self::restream($liveTransmitionHistory_id);', $method);
    }

    public function testBackgroundScriptOnlyRunsFromTheCommandLine()
    {
        $script = file_get_contents(\APP_ROOT . '/plugin/Live/restreamInBackground.php');
        $this->assertMatchesRegularExpression('/if \(!isCommandLineInterface\(\)\) \{\s*return die\(/', $script);
        $this->assertLessThan(strpos($script, 'Live::restream('), strpos($script, 'isCommandLineInterface()'));
        $this->assertStringContainsString('intval(@$argv[1])', $script);
    }

    public function testBackgroundCommandQuotesThePhpExecutableAndDoesNotRestreamInline()
    {
        $this->assertTrue(RestreamBackgroundLive::restreamInBackground('42'));
        $this->assertSame([], RestreamBackgroundState::$restreams);
        $this->assertSame([
            escapeshellarg(RestreamBackgroundState::$php) . ' '
                . escapeshellarg('/opt/site with spaces/plugin/Live/restreamInBackground.php') . ' '
                . escapeshellarg('42'),
        ], RestreamBackgroundState::$commands);
    }

    public function testWindowsAndMissingExecKeepTheSynchronousFallback()
    {
        RestreamBackgroundState::$windows = true;
        $this->assertSame('synchronous', RestreamBackgroundLive::restreamInBackground(42));
        RestreamBackgroundState::$windows = false;
        RestreamBackgroundState::$execAvailable = false;
        $this->assertSame('synchronous', RestreamBackgroundLive::restreamInBackground(43));
        $this->assertSame([42, 43], RestreamBackgroundState::$restreams);
        $this->assertSame([], RestreamBackgroundState::$commands);
    }

    public function testGeneratedCommandRunsWithSpacesInThePhpAndScriptPaths()
    {
        if (PHP_OS_FAMILY !== 'Linux' || !\function_exists('proc_open')) {
            $this->markTestSkipped('Needs a POSIX shell and symlink support.');
        }
        $directory = sys_get_temp_dir() . '/avideo restream ' . bin2hex(random_bytes(6));
        $script = $directory . '/plugin/Live/restreamInBackground.php';
        mkdir(dirname($script), 0777, true);
        symlink(PHP_BINARY, $directory . '/custom php');
        file_put_contents($script, '<?php echo $argv[1];');
        try {
            RestreamBackgroundState::$php = $directory . '/custom php';
            $GLOBALS['global']['systemRootPath'] = $directory . '/';
            RestreamBackgroundLive::restreamInBackground(42);
            $process = proc_open(RestreamBackgroundState::$commands[0], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $this->assertIsResource($process);
            $output = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $errors);
            $this->assertSame('42', $output);
        } finally {
            unlink($script);
            unlink($directory . '/custom php');
            rmdir(dirname($script));
            rmdir($directory . '/plugin');
            rmdir($directory);
        }
    }
}
