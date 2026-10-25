<?php

namespace Tests\Unit\RequestDiagnostics;

use PHPUnit\Framework\TestCase;

// Isolate the real logging helpers from the suite's _error_log mock.
$requestDiagnosticsSource = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsLogs.php');
eval('namespace ' . __NAMESPACE__ . '; ' . substr($requestDiagnosticsSource, 5));
unset($requestDiagnosticsSource);

class RequestDiagnosticsTest extends TestCase
{
    public function testCorrelationIdIsStableAndServerGenerated(): void
    {
        $_SERVER['HTTP_X_AVIDEO_REQUEST_ID'] = 'untrusted-client-id';
        $this->assertMatchesRegularExpression('/^[a-f0-9]{24}$/', avideoRequestId());
        $this->assertSame(avideoRequestId(), avideoRequestId());
        $this->assertNotSame($_SERVER['HTTP_X_AVIDEO_REQUEST_ID'], avideoRequestId());
        unset($_SERVER['HTTP_X_AVIDEO_REQUEST_ID']);
    }

    public function testMessagesDropValuesUrlsAndNativeStackArguments(): void
    {
        $message = "Uncaught Error: failed 'PRIVATE-VALUE' https://example.test/private?token=URL-SECRET password=PASS-SECRET\nStack trace:\n#0 path.php(1): login('STACK-SECRET')";
        $sanitized = avideoDiagnosticMessage($message);
        foreach (['PRIVATE-VALUE', 'URL-SECRET', 'PASS-SECRET', 'STACK-SECRET', "\n"] as $secret) {
            $this->assertStringNotContainsString($secret, $sanitized);
        }
        $this->assertStringContainsString('Uncaught Error: failed', $sanitized);
        $this->assertSame('Uncaught mysqli_sql_exception: Unknown column x in field list', avideoDiagnosticMessage("Uncaught mysqli_sql_exception: Unknown column x in field list in /var/www/html/objects/video.php:12\nStack trace:\n#0 {main}"));
        $this->assertStringNotContainsString('BEARER-SECRET', avideoDiagnosticMessage('Authorization: Bearer BEARER-SECRET'));
        $this->assertStringNotContainsString('COOKIE-SECRET', avideoDiagnosticMessage('Cookie: session=COOKIE-SECRET; token=OTHER'));
    }

    public function testHandledExceptionLogsLocationAndTraceWithoutRequestData(): void
    {
        $requestDiagnosticsLog = tempnam(sys_get_temp_dir(), 'avideo-diagnostic-');
        $previousLog = ini_get('error_log');
        $previousGlobal = $GLOBALS['global'] ?? [];
        $previousServer = $_SERVER;
        ini_set('error_log', $requestDiagnosticsLog);
        $GLOBALS['global'] = ['systemRootPath' => dirname(__DIR__, 2) . '/', 'printLogs' => true];
        $_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__, 2) . '/objects/example.json.php';
        $_SERVER['REQUEST_URI'] = '/objects/example.json.php?pass=QUERY-SECRET';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer HEADER-SECRET';
        $_POST = ['password' => 'BODY-SECRET'];
        try {
            ob_start();
            try { throw new \RuntimeException("Failed operation 'MESSAGE-SECRET'"); }
            catch (\Throwable $exception) { avideoLogException($exception); }
            $this->assertSame('', ob_get_clean(), 'Internal diagnostics must not enter a response');
            $this->assertTrue($GLOBALS['global']['printLogs']);
            $log = file_get_contents($requestDiagnosticsLog);
            foreach (['QUERY-SECRET', 'HEADER-SECRET', 'BODY-SECRET', 'MESSAGE-SECRET', '"args"'] as $secret) {
                $this->assertStringNotContainsString($secret, $log);
            }
            $this->assertStringContainsString('"event":"exception"', $log);
            $this->assertStringContainsString('"script":"objects/example.json.php"', $log);
            $this->assertStringContainsString('"class":"RuntimeException"', $log);
            $this->assertStringContainsString('"line":', $log);
            $this->assertStringContainsString('RequestDiagnostic Caught RuntimeException: Failed operation [value redacted] at tests/Unit/RequestDiagnosticsTest.php:', $log);
            $this->assertMatchesRegularExpression('#"trace":\["[^"]+\(\d+\): [^"]+\(\)"#', $log);
        } finally {
            ini_set('error_log', $previousLog);
            $GLOBALS['global'] = $previousGlobal;
            $_SERVER = $previousServer;
            $_POST = [];
            unlink($requestDiagnosticsLog);
        }
    }

    public function testRealHttpShutdownRecords500AndFatalButNot200(): void
    {
        $diagnosticsHttpDirectory = sys_get_temp_dir() . '/avideo-diagnostic-http-' . bin2hex(random_bytes(6));
        mkdir($diagnosticsHttpDirectory . '/tmp', 0700, true);
        $diagnosticsHttpLog = $diagnosticsHttpDirectory . '/errors.log';
        // Minimal functions.php helpers so rateLimitedLog() runs; its state stays in this sys_temp_dir.
        $diagnosticsHttpSource = '<?php $global = []; function isCommandLineInterface() { return false; } '
            . 'function make_path($path) { return is_dir($path) || mkdir($path, 0700, true); } '
            . 'function _file_put_contents($file, $content) { return file_put_contents($file, $content); } '
            . 'ini_set("error_log", ' . var_export($diagnosticsHttpLog, true) . '); '
            . 'require ' . var_export(dirname(__DIR__, 2) . '/objects/functionsLogs.php', true) . '; '
            . 'if (($_GET["case"] ?? "") === "fatal") { trigger_error("Synthetic failure \'FATAL-SECRET\'", E_USER_ERROR); } '
            . 'function diagnosticsUpload($password) { throw new RuntimeException("Upload failed"); } '
            . 'if (($_GET["case"] ?? "") === "uncaught") { diagnosticsUpload("ARG-SECRET"); } '
            . 'if (($_GET["case"] ?? "") === "500") { _error_log("Unable to save live audience sample"); } '
            . 'http_response_code(($_GET["case"] ?? "") === "500" ? 500 : 200); echo "generic-response";';
        file_put_contents($diagnosticsHttpDirectory . '/index.php', $diagnosticsHttpSource);
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $process = proc_open([PHP_BINARY, '-d', 'display_errors=0', '-d', 'log_errors=0', '-d', 'sys_temp_dir=' . $diagnosticsHttpDirectory . '/tmp', '-S', $address, '-t', $diagnosticsHttpDirectory], [0 => ['pipe', 'r'], 1 => ['file', $diagnosticsHttpDirectory . '/server.log', 'a'], 2 => ['file', $diagnosticsHttpDirectory . '/server.log', 'a']], $pipes);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        try {
            for ($attempt = 0; $attempt < 50; $attempt++) {
                $ready = @stream_socket_client('tcp://' . $address, $errno, $error, 0.1);
                if ($ready) { fclose($ready); break; }
                usleep(100000);
            }
            $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
            $this->assertSame('generic-response', file_get_contents('http://' . $address . '/?case=200', false, $context));
            $this->assertFalse(file_exists($diagnosticsHttpLog), 'Successful requests should not add error diagnostics');
            $this->assertSame('generic-response', file_get_contents('http://' . $address . '/?case=500&pass=QUERY-SECRET', false, $context));
            $headers = implode("\n", $http_response_header);
            $this->assertMatchesRegularExpression('/X-AVideo-Request-ID: [a-f0-9]{24}/i', $headers);
            $this->assertStringContainsString('500', $headers);
            $this->assertStringContainsString('"event":"http_error"', file_get_contents($diagnosticsHttpLog));
            $this->assertSame('generic-response', file_get_contents('http://' . $address . '/?case=500', false, $context));
            $fatalBody = file_get_contents('http://' . $address . '/?case=fatal', false, $context);
            $this->assertStringNotContainsString('FATAL-SECRET', $fatalBody);
            for ($attempt = 0; $attempt < 20; $attempt++) {
                if (strpos(file_get_contents($diagnosticsHttpLog), '"event":"php_fatal"') !== false) { break; }
                usleep(50000);
            }
            file_get_contents('http://' . $address . '/?case=fatal', false, $context);
            usleep(200000);
            $log = file_get_contents($diagnosticsHttpLog);
            $this->assertSame(1, substr_count($log, '"event":"http_error"'), 'A repeated 5xx must not flood the log');
            $this->assertSame(1, substr_count($log, '"event":"php_fatal"'), 'A repeated fatal must not flood the log');
            $this->assertStringNotContainsString('FATAL-SECRET', $log);
            $this->assertStringNotContainsString('QUERY-SECRET', $log);
            $this->assertStringContainsString('RequestDiagnostic HTTP 500 returned by ', $log);
            $this->assertStringContainsString('"recent_log":["Unable to save live audience sample"]', $log);
            $this->assertStringContainsString('RequestDiagnostic PHP Fatal error (trigger_error): Synthetic failure [value redacted] at ', $log);

            $uncaughtBody = file_get_contents('http://' . $address . '/?case=uncaught', false, $context);
            $this->assertStringNotContainsString('ARG-SECRET', $uncaughtBody);
            for ($attempt = 0; $attempt < 20; $attempt++) {
                if (substr_count(file_get_contents($diagnosticsHttpLog), '"event":"php_fatal"') > 1) { break; }
                usleep(50000);
            }
            $log = file_get_contents($diagnosticsHttpLog);
            $this->assertStringContainsString('RequestDiagnostic PHP Fatal error: Uncaught RuntimeException: Upload failed at ', $log);
            $this->assertMatchesRegularExpression('#"trace":\["[^"]*index\.php\(\d+\): diagnosticsUpload\(\)"#', $log);
            $this->assertStringNotContainsString('ARG-SECRET', $log);
        } finally {
            proc_terminate($process);
            proc_close($process);
            $diagnosticsHttpFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($diagnosticsHttpDirectory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($diagnosticsHttpFiles as $diagnosticsHttpFile) {
                $diagnosticsHttpFile->isDir() ? rmdir($diagnosticsHttpFile->getPathname()) : unlink($diagnosticsHttpFile->getPathname());
            }
            rmdir($diagnosticsHttpDirectory);
        }
    }
}
