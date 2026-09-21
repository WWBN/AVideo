<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/InstallerTestEnvironment.php';

class EncoderInstallerAuthenticationTest extends TestCase
{
    /** @dataProvider credentialCases */
    public function testInstallerWithStreamerPasswordVerifier($password, $salted, $admin, $accepted)
    {
        if (!extension_loaded('curl')) { $this->markTestSkipped('PHP curl is required.'); }
        $root = dirname(__DIR__, 2);
        $fixture = new InstallerTestEnvironment($root . '/.compose/encoder');
        try {
            // Use the real verifier: a mock accepting encoded hashes concealed this regression.
            $source = file_get_contents($root . '/objects/functions.php');
            $start = strpos($source, 'function encryptPassword(');
            $end = strpos($source, 'function isMobile(', $start);
            $this->assertNotFalse($start);
            $this->assertNotFalse($end);
            $fixture->write('verifier.php', "<?php\n" . substr($source, $start, $end - $start));
            $fixture->write('settings.json', json_encode(['salted' => $salted, 'admin' => $admin]));
            $fixture->write('router.php', <<<'PHP'
<?php
require __DIR__ . '/verifier.php';
class User {
    public static function getPasswordFromUserHashIfTheItIsValid($value) { return false; }
}
$settings = json_decode(file_get_contents(__DIR__ . '/settings.json'), true);
$advancedCustomUser = (object) ['encryptPasswordsWithSalt' => $settings['salted']];
$global = ['salt' => 'test-site-specific-salt'];
$valid = ($_POST['user'] ?? '') === 'admin'
    && encryptPasswordVerify($_POST['pass'] ?? '', encryptPassword('correct-password'), $_POST['encodedPass'] ?? false);
header('Content-Type: application/json');
echo json_encode(['isLogged' => $valid, 'isAdmin' => $valid && $settings['admin']]);
PHP
            );
            $url = $fixture->startRouter('router.php');
            $ready = false;
            for ($attempt = 0; $attempt < 50; ++$attempt) {
                if (@file_get_contents($url) !== false) { $ready = true; break; }
                usleep(100000);
            }
            $this->assertTrue($ready, 'Test Streamer did not start.');
            $fixture->write('request.json', json_encode(['siteURL' => $url, 'inputUser' => 'admin', 'inputPassword' => $password]));
            $fixture->write('verify.php', <<<'PHP'
<?php
require __DIR__ . '/install/installer.php';
try {
    installerValidateStreamer(json_decode(file_get_contents(__DIR__ . '/request.json'), true));
    echo 'ACCEPTED';
} catch (InstallerFailure $e) {
    echo 'REJECTED';
    exit(1);
}
PHP
            );
            $result = $fixture->run([$fixture->path('verify.php')]);
            $this->assertSame($accepted ? 0 : 1, $result['exit'], $result['stderr']);
            $this->assertSame($accepted ? 'ACCEPTED' : 'REJECTED', $result['stdout']);
            $this->assertStringNotContainsString($password, $result['stdout'] . $result['stderr']);
        } finally {
            $fixture->cleanup();
        }
    }

    public function credentialCases(): array
    {
        return [
            'correct password' => ['correct-password', false, true, true],
            'salted password' => ['correct-password', true, true, true],
            'wrong password' => ['wrong-password', false, true, false],
            'non administrator' => ['correct-password', false, false, false],
            'database hash is not a password' => [md5(hash('whirlpool', sha1('correct-password'))), false, true, false],
        ];
    }
}
