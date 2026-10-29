<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Run the standalone media-host endpoint over HTTP, without a Streamer bootstrap. */
class LiveRecordedFileTransferTest extends TestCase
{
    private $directory;
    private $server;
    private $url;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/avideo-recorded-http-' . bin2hex(random_bytes(8));
        mkdir($this->directory, 0700);
        copy(dirname(__DIR__, 2) . '/plugin/Live/standAloneFiles/getRecordedFile.php', $this->directory . '/getRecordedFile.php');
        file_put_contents($this->directory . '/recording.flv', 'FLV' . str_repeat("\0", 1024));
        $this->writeEnvironment('fixture-secret');
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        self::assertIsResource($socket, $error);
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $this->url = 'http://' . $address . '/getRecordedFile.php';
        $this->server = proc_open([PHP_BINARY, '-S', $address, '-t', $this->directory], [
            0 => ['pipe', 'r'],
            1 => ['file', $this->directory . '/server.log', 'a'],
            2 => ['file', $this->directory . '/server.log', 'a'],
        ], $pipes);
        self::assertIsResource($this->server);
        fclose($pipes[0]);
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $ready = @stream_socket_client('tcp://' . $address, $errno, $error, 0.05);
            if ($ready) {
                fclose($ready);
                return;
            }
            usleep(10000);
        }
        self::fail('Standalone HTTP server did not start');
    }

    protected function tearDown(): void
    {
        if (is_resource($this->server)) {
            proc_terminate($this->server);
            proc_close($this->server);
        }
        foreach (glob($this->directory . '/*') as $file) { unlink($file); }
        unlink($this->directory . '/.env');
        rmdir($this->directory);
    }

    private function writeEnvironment($secret): void
    {
        file_put_contents($this->directory . '/.env', 'RECORD_PATH=' . $this->directory . "/\nSECRET_KEY=" . $secret . "\n");
    }

    private function request($query): array
    {
        $context = stream_context_create(['http' => ['ignore_errors' => true, 'follow_location' => 0, 'timeout' => 3]]);
        $stream = fopen($this->url . '?' . http_build_query($query), 'rb', false, $context);
        $body = stream_get_contents($stream);
        $metadata = stream_get_meta_data($stream);
        fclose($stream);
        preg_match('/^HTTP\/\S+ (\d+)/', $metadata['wrapper_data'][0], $status);
        return [(int) $status[1], $body];
    }

    /** @dataProvider deniedRequests */
    public function testMissingOrWrongSecretIsAnHttpFailure($query)
    {
        self::assertSame([403, 'forbidden'], $this->request($query));
    }

    public function deniedRequests(): array
    {
        return [[['file' => 'recording']], [['file' => 'recording', 'secret' => 'wrong']]];
    }

    public function testUnconfiguredSecretRemainsDenied()
    {
        $this->writeEnvironment('');
        self::assertSame([403, 'forbidden'], $this->request(['file' => 'recording', 'secret' => 'fixture-secret']));
    }

    public function testAuthorizedTransferPreservesTheBinaryFile()
    {
        self::assertSame([200, file_get_contents($this->directory . '/recording.flv')], $this->request(['file' => 'recording', 'secret' => 'fixture-secret']));
        self::assertFileExists($this->directory . '/recording.flv');
    }

    public function testMissingRecordingIsAnHttpFailure()
    {
        self::assertSame([404, 'file does not exists'], $this->request(['file' => 'absent', 'secret' => 'fixture-secret']));
    }

    public function testMissingFileParameterIsAnHttpFailure()
    {
        self::assertSame([400, 'file not found'], $this->request(['secret' => 'fixture-secret']));
    }
}
