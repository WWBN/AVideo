<?php

namespace Tests\Security;

use Tests\TestCase;

class UserAccountMutationLockTest extends TestCase
{
    public function testConcurrentPromotionAndLockLifetime()
    {
        $fixture = dirname(__DIR__) . '/fixtures/user-account-mutation-regression.php';
        $process = proc_open([PHP_BINARY, $fixture], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $output . $errors);
        $this->assertSame("Account mutation regression: passed\n", $output);
        $this->assertSame('', $errors);
    }
}
