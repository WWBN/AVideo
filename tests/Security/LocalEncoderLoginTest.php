<?php
namespace Tests\Security\LocalEncoderLogin;

use PHPUnit\Framework\TestCase;

$localEncoderTestRoot = dirname(__DIR__, 2);
$localEncoderGrantSource = file_get_contents($localEncoderTestRoot . '/plugin/CustomizeAdvanced/LocalEncoderLogin.php');
eval('namespace ' . __NAMESPACE__ . '; use \DirectoryIterator; use \RuntimeException; use \InvalidArgumentException;' . substr($localEncoderGrantSource, 5));

class Denied extends \RuntimeException {}
class CustomizeAdvanced { public static $enabled = true; public static function getLocalEncoderURL() { return self::$enabled ? 'https://video.test/' : ''; } }
class User {
    public static $logged = true;
    public static $upload = true;
    public static $password = 'test-password-version';
    public static $status = 'a';
    private $id;
    public function __construct($id) { $this->id = $id; }
    public static function isLogged() { return self::$logged; }
    public static function canUpload() { return self::$upload; }
    public static function getId() { return 7; }
    public static function getUserHash($id, $ttl) { return '_user_hash_test_' . $id; }
    public function getPassword() { return self::$password; }
    public function getStatus() { return self::$status; }
    public function getUser() { return 'alice'; }
}
class AVideoLog { public static $ERROR = 1; }
function header($value) {}
function __($value) { return $value; }
function forbidIfNotPost() { if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { throw new Denied('method'); } }
function forbiddenPage($message, $log) { throw new Denied($message); }
function isGlobalTokenValid() { return $GLOBALS['localEncoderTestCSRF']; }
function _error_log($message, $level) { $GLOBALS['localEncoderTestLog'][] = $message; }

class LocalEncoderLoginTest extends TestCase
{
    private $directory;
    private $saved;
    private $source;
    private $verifier = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    protected function setUp(): void
    {
        $this->saved = array($_POST, $_REQUEST, $_SERVER, $GLOBALS['global'] ?? null);
        $GLOBALS['global'] = array('systemRootPath' => sys_get_temp_dir() . '/local-encoder-unit-' . bin2hex(random_bytes(10)));
        $this->directory = rtrim(sys_get_temp_dir(), '/\\') . '/avideo-local-login-' . hash('sha256', $GLOBALS['global']['systemRootPath']);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $_REQUEST = array();
        $GLOBALS['localEncoderTestCSRF'] = true;
        $GLOBALS['localEncoderTestLog'] = array();
        User::$logged = User::$upload = CustomizeAdvanced::$enabled = true;
        User::$password = 'test-password-version';
        User::$status = 'a';
        $this->source = preg_replace('/^require_once[^\r\n]*\r?$/m', '', substr(file_get_contents(dirname(__DIR__, 2) . '/plugin/CustomizeAdvanced/localEncoderLogin.json.php'), 5));
    }
    protected function tearDown(): void
    {
        if (is_dir($this->directory)) {
            foreach (glob($this->directory . '/*.json') as $file) { unlink($file); }
            rmdir($this->directory);
        }
        list($_POST, $_REQUEST, $_SERVER, $GLOBALS['global']) = $this->saved;
        unset($GLOBALS['localEncoderTestCSRF'], $GLOBALS['localEncoderTestLog']);
    }
    private function endpoint($data)
    {
        global $global;
        $_POST = $_REQUEST = $data;
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; use \Throwable;' . $this->source);
            return json_decode(ob_get_contents(), true);
        } finally { ob_end_clean(); }
    }
    private function issue()
    {
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $this->verifier, true)), '+/', '-_'), '=');
        return $this->endpoint(array('action' => 'issue', 'challenge' => $challenge, 'users_id' => 99));
    }
    public function testTheGrantIsBoundToTheCurrentAccountAndProofKeyAndCanOnlyBeRedeemedOnce(): void
    {
        $grant = $this->issue();
        $this->assertFalse($grant['error']);
        $this->assertSame(120, $grant['expires_in']);
        $record = file_get_contents(glob($this->directory . '/*.json')[0]);
        $this->assertStringNotContainsString($grant['code'], $record);
        $this->assertStringNotContainsString(User::$password, $record);
        $this->assertStringNotContainsString('_user_hash_', $record);
        User::$logged = false;
        $this->assertTrue($this->endpoint(array('action' => 'redeem', 'code' => $grant['code'], 'verifier' => str_repeat('b', 43)))['error']);
        $result = $this->endpoint(array('action' => 'redeem', 'code' => $grant['code'], 'verifier' => $this->verifier));
        $this->assertSame(array('error' => false, 'user' => 'alice', 'pass' => '_user_hash_test_7'), $result);
        $this->assertTrue($this->endpoint(array('action' => 'redeem', 'code' => $grant['code'], 'verifier' => $this->verifier))['error']);
    }
    public function testExpiredGrantsDoNotAuthenticate(): void
    {
        $grant = $this->issue();
        $file = glob($this->directory . '/*.json')[0];
        $record = json_decode(file_get_contents($file), true);
        $record['expires'] = time() - 1;
        file_put_contents($file, json_encode($record));
        $this->assertTrue($this->endpoint(array('action' => 'redeem', 'code' => $grant['code'], 'verifier' => $this->verifier))['error']);
    }
    public function testAChangedPasswordOrDisabledAccountRevokesTheGrant(): void
    {
        $grant = $this->issue();
        User::$password = 'changed-version';
        $this->assertTrue($this->endpoint(array('action' => 'redeem', 'code' => $grant['code'], 'verifier' => $this->verifier))['error']);
        $grant = $this->issue();
        User::$status = 'i';
        $this->assertTrue($this->endpoint(array('action' => 'redeem', 'code' => $grant['code'], 'verifier' => $this->verifier))['error']);
    }
    public function testBrowserIssuanceRequiresCSRFIntent(): void
    {
        $GLOBALS['localEncoderTestCSRF'] = false;
        $this->expectException(Denied::class);
        $this->issue();
    }
    public function testTheVerifiedEncoderCredentialPathDoesNotRequireBrowserCSRF(): void
    {
        $GLOBALS['localEncoderTestCSRF'] = false;
        $GLOBALS['global']['loggedInFromRequestCredentials'] = true;
        $this->assertFalse($this->issue()['error']);
    }
    public function testAnAnonymousCallerCannotIssueAGrant(): void
    {
        User::$logged = false;
        $this->expectException(Denied::class);
        $this->issue();
    }
    public function testAnAccountWithoutUploadPermissionCannotIssueAGrant(): void
    {
        User::$upload = false;
        $this->expectException(Denied::class);
        $this->issue();
    }
    public function testTheAdministratorCanDisableIssuanceAndRedemption(): void
    {
        CustomizeAdvanced::$enabled = false;
        $this->expectException(Denied::class);
        $this->issue();
    }
    public function testGetCannotIssueAGrant(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->expectException(Denied::class);
        $this->issue();
    }
    public function testMalformedOrOversizedProofsFailWithoutDisclosingData(): void
    {
        $this->assertTrue($this->endpoint(array('action' => 'redeem', 'code' => '../private', 'verifier' => str_repeat('a', 5000)))['error']);
        $this->assertTrue($this->endpoint(array('action' => 'issue', 'challenge' => array('invalid')) )['error']);
        $this->assertStringNotContainsString(User::$password, json_encode($GLOBALS['localEncoderTestLog']));
    }
}
