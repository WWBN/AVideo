<?php

use PHPUnit\Framework\TestCase;

class ApiAuthenticationRegressionTest extends TestCase
{
    /** @dataProvider regressionScripts */
    public function testAuthenticationWithIsolatedFixtures($script, $expectedOutput)
    {
        // Each script exercises production methods in a separate PHP process so
        // its User/ApiObject fixtures cannot replace other tests' classes.
        $output = [];
        $exitCode = 0;
        exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(dirname(__DIR__) . '/' . $script) . ' 2>&1', $output, $exitCode);
        $this->assertSame(0, $exitCode, implode("\n", $output));
        $this->assertSame([$expectedOutput], $output);
    }

    public function regressionScripts(): array
    {
        return [
            'deactivation reauthentication' => ['api-deactivation-regression.php', 'API deactivation regression: passed'],
            'session request budget' => ['api-session-rate-limit-regression.php', 'API session rate-limit regression: passed'],
            'account deletion' => ['api-user-delete-regression.php', 'API user delete regression: passed'],
            'set CSRF guard' => ['api-set-csrf-guard-regression.php', 'API set CSRF guard regression: passed'],
            'failed login penalty' => ['login-failed-attempts-regression.php', 'Login failed-attempts regression: passed'],
        ];
    }
}
