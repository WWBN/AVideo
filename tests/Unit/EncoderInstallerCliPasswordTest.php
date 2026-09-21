<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/InstallerTestEnvironment.php';

class EncoderInstallerCliPasswordTest extends TestCase
{
    /** @dataProvider passwordCases */
    public function testPasswordSelectionAndPrompt($expected, $input, $environmentPassword, $argumentPassword, $success, $prompt)
    {
        if (!extension_loaded('curl')) { $this->markTestSkipped('PHP curl is required.'); }
        $fixture = new InstallerTestEnvironment(dirname(__DIR__, 2) . '/.compose/encoder');
        try {
            $fixture->write('expected.txt', $expected);
            $fixture->write('router.php', <<<'PHP'
<?php
header('Content-Type: application/json');
$expected = md5(hash('whirlpool', sha1(file_get_contents(__DIR__ . '/expected.txt'))));
echo json_encode(['isAdmin' => ($_POST['user'] ?? '') === 'admin'
    && ($_POST['encodedPass'] ?? '') === 'true' && ($_POST['pass'] ?? '') === $expected]);
PHP
            );
            // Stop before database installation; verify the selected password reaches both fields.
            $fixture->write('install/checkConfiguration.php', <<<'PHP'
<?php
$expected = file_get_contents(__DIR__ . '/../expected.txt');
$installerSucceeded = $_POST['inputPassword'] === $expected && $_POST['systemAdminPass'] === $expected;
echo $installerSucceeded ? 'READY_TO_INSTALL' : 'WRONG_PASSWORD';
PHP
            );
            $url = $fixture->startRouter('router.php');
            $ready = false;
            for ($attempt = 0; $attempt < 50; ++$attempt) {
                if (@file_get_contents($url) !== false) { $ready = true; break; }
                usleep(100000);
            }
            $this->assertTrue($ready, 'Mock Streamer did not start.');
            $arguments = [$fixture->path('install/install.php'), $url];
            if ($argumentPassword !== null) { $arguments = array_merge($arguments, ['db-user', 'db-password', $argumentPassword]); }
            $result = $fixture->run($arguments, $input, ['STREAMER_PASSWORD' => $environmentPassword]);
            $this->assertSame($success ? 0 : 1, $result['exit'], $result['stderr']);
            $this->assertSame($success, strpos($result['stdout'], 'READY_TO_INSTALL') !== false);
            $this->assertSame($prompt, strpos($result['stderr'], 'Enter the Streamer administrator password') !== false);
            $this->assertStringNotContainsString($expected, $result['stdout'] . $result['stderr']);
        } finally {
            $fixture->cleanup();
        }
    }

    public function passwordCases(): array
    {
        return [
            'default accepted' => ['123', '', null, null, true, false],
            'prompt accepted preserves spaces' => [' other password ', " other password \n", null, null, true, true],
            'wrong prompt rejected' => ['correct-password', "wrong-password\n", null, null, false, true],
            'empty cancels' => ['correct-password', "\n", null, null, false, true],
            'EOF cancels' => ['correct-password', '', null, null, false, true],
            'environment retained' => ['environment-password', '', 'environment-password', null, true, false],
            'argument takes precedence' => ['argument-password', '', 'environment-password', 'argument-password', true, false],
        ];
    }
}
