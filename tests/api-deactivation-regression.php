<?php
// Execute the real handler with isolated fixtures; never modify a real account.
class User {
    public static $verifiedId = 0;
    public static $saved = 0;
    public function __construct($id, $user = '', $password = '') {}
    public function getUser() { return 'fixture'; }
    public function getStatus() { return 'a'; }
    public static function getId() { return 7; } // Existing authenticated session.
    public static function isAdmin() { return false; }
    public function login($noPass, $encoded) { throw new RuntimeException('Must not trust login() for reauthentication'); }
    public function getVerifiedCredentialsUserId($encoded) { return self::$verifiedId; }
    public function setStatus($status) { if ($status !== 'i') throw new RuntimeException('Unexpected status'); }
    public function save() { ++self::$saved; return true; }
}
class ApiObject {
    public $error;
    public $response;
    public function __construct($message = '', $error = true, $response = null) { $this->error = $error; $this->response = $response; }
}
class Captcha { public static function validation($code) { return $code === 'fixture'; } }
function _error_log($message) {}
$root = sys_get_temp_dir() . '/avideo-deactivation-' . bin2hex(random_bytes(6));
mkdir($root . '/objects', 0700, true);
file_put_contents($root . '/objects/captcha.php', '<?php');
$global = ['systemRootPath' => $root . '/'];
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
try {
    $source = file_get_contents(dirname(__DIR__) . '/plugin/API/API.php');
    $start = strpos($source, '    public function set_api_user_inactive(');
    $end = strpos($source, '    private function checkRateLimit(', $start);
    eval('class DeactivationFixture {' . substr($source, $start, $end - $start) . '
        public $budget = 0;
        public static function isAPISecretValid() { return false; }
        private function checkRateLimit($op, $limit, $window) { ++$this->budget; }
    }');
    foreach ([0 => true, 8 => true, 7 => false] as $verifiedId => $expectedError) {
        User::$verifiedId = $verifiedId; User::$saved = 0;
        $api = new DeactivationFixture();
        $result = $api->set_api_user_inactive(['users_id' => 7, 'user' => 'fixture', 'pass' => 'fixture', 'captcha' => 'fixture']);
        if ($result->error !== $expectedError || User::$saved !== ($expectedError ? 0 : 1) || $api->budget !== 1) {
            throw new RuntimeException('Credential/target verification or rate limiting failed');
        }
    }
    // Exercise the production credential wrapper and ensure it requires an active
    // account via find(), without creating a session.
    $source = file_get_contents(dirname(__DIR__) . '/objects/user.php');
    $start = strpos($source, '    public function getVerifiedCredentialsUserId(');
    $end = strpos($source, '    public function login(', $start);
    eval('class CredentialFixture {' . substr($source, $start, $end - $start) . '
        public static $result;
        private $user = "fixture";
        private $password = "fixture";
        private function find($user, $password, $active, $encoded) {
            if (!$active || $user !== "fixture" || $password !== "fixture" || !$encoded) throw new RuntimeException("Wrong credential verification inputs");
            return self::$result;
        }
    }');
    CredentialFixture::$result = ["id" => "7", "status" => "a"];
    if ((new CredentialFixture())->getVerifiedCredentialsUserId(true) !== 7) throw new RuntimeException('Invalid verified ID');
    foreach ([false, [], ["id" => "7", "status" => "i"], ["id" => "7"]] as $result) {
        CredentialFixture::$result = $result;
        if ((new CredentialFixture())->getVerifiedCredentialsUserId(true) !== 0) {
            throw new RuntimeException('Missing or inactive users must not pass credential verification');
        }
    }
    echo "API deactivation regression: passed\n";
} finally {
    unlink($root . '/objects/captcha.php'); rmdir($root . '/objects'); rmdir($root);
}
