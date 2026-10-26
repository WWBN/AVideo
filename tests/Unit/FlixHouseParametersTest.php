<?php

use PHPUnit\Framework\TestCase;

class FlixHouseParametersTest extends TestCase
{
    public function testOptionalPluginHandlesMissingAndCachedVideoMetadata()
    {
        $script = dirname(__DIR__, 2) . '/plugin/Flixhouse/tests/get-parameters-regression.php';
        if (!is_file($script)) {
            $this->markTestSkipped('The separately distributed FlixHouse plugin regression script is not installed.');
        }
        $process = proc_open([PHP_BINARY, $script], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertSame('', $errors);
        $this->assertStringContainsString('PASS:', $output);
    }
}
