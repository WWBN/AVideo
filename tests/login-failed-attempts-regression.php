<?php
// Executes the real failed-login penalty from objects/user.php with an in-memory counter.
// No database, live credentials, or server mutation is involved.
class AVideoLog { public static $SECURITY = 'security'; }
$store = [];
$ip = '198.51.100.9';
function isCommandLineInterface() { return false; }
function getRealIpAddr() { return $GLOBALS['ip']; }
function _error_log($msg, $level = '') {}
function rateLimitIncrementAndGet(string $key, int $window): int { $GLOBALS['store'][$key] = ($GLOBALS['store'][$key] ?? 0) + 1; return $GLOBALS['store'][$key]; }
function rateLimitDecrement(string $key, int $window): void {
    if (!empty($GLOBALS['store'][$key])) {
        --$GLOBALS['store'][$key];
    }
}

$root = dirname(__DIR__);
$source = file_get_contents($root . '/objects/user.php');
$start = strpos($source, '    private static function getFailedLoginBuckets(');
$end = strpos($source, '    public static function isCaptchaNeed()', $start);
if ($start === false || $end === false) {
    throw new RuntimeException('failed-login helpers not found');
}
eval('class ThrottleFixture {
    const FAILED_LOGIN_WINDOW = 900;
    private static $failedLoginState = [];
    public static function newRequest() { self::$failedLoginState = []; }
    // mirrors User::login(): reserve before the password check, refund only when it is right
    public static function attempt($user, $passwordIsRight) {
        self::newRequest();
        if (!self::reserveLoginAttempt($user)) {
            return "blocked";
        }
        if ($passwordIsRight) {
            self::refundLoginAttempt($user);
            return "logged";
        }
        return "failed";
    }
    public static function reserve($user) { return self::reserveLoginAttempt($user); }
' . substr($source, $start, $end - $start) . '}');

function expect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// successful logins never count, however many there are
for ($i = 0; $i < 200; ++$i) {
    expect(ThrottleFixture::attempt('alice', true) === 'logged', 'successful login was throttled');
}
expect(array_sum($store) === 0, 'successful logins were counted');

// 10 wrong passwords for one account are allowed, the 11th must wait - even with the right password
for ($i = 0; $i < 10; ++$i) {
    expect(ThrottleFixture::attempt('Alice', false) === 'failed', "failure $i was blocked too early");
}
expect(ThrottleFixture::attempt(' alice ', true) === 'blocked', 'account was not blocked after 10 failures');
// a blocked attempt checks no password, so it must not extend the penalty
expect($store["login_failed_account_{$ip}_alice"] === 10 && $store["login_failed_ip_{$ip}"] === 10, 'blocked attempt was counted');
expect(ThrottleFixture::attempt('bob', true) === 'logged', 'other accounts were blocked by one account penalty');

// the same credentials re-checked by several layers in one request count once
ThrottleFixture::newRequest();
expect(ThrottleFixture::reserve('carol') && ThrottleFixture::reserve('carol'), 'second check in one request was refused');
expect($store["login_failed_account_{$ip}_carol"] === 1, 'one request was counted twice');

// password spraying: many accounts from one IP stop at 100 failures in total
for ($n = 0; $store["login_failed_ip_{$ip}"] < 100; ++$n) {
    expect(ThrottleFixture::attempt("user$n", false) === 'failed', 'IP blocked before 100 failures');
}
expect(ThrottleFixture::attempt('dave', true) === 'blocked', 'IP was not blocked after 100 failures');

// other IPs are unaffected, and the penalty ends with the window
$ip = '198.51.100.10';
expect(ThrottleFixture::attempt('alice', true) === 'logged', 'penalty leaked to another IP');
$ip = '198.51.100.9';
$store = [];
expect(ThrottleFixture::attempt('alice', true) === 'logged', 'penalty did not end with the window');

// login() must reserve before checking the password and refund only for valid credentials
$loginStart = strpos($source, '    public function login(');
$login = substr($source, $loginStart, strpos($source, 'static function setUserCookie(', $loginStart) - $loginStart);
$reserveAt = strpos($login, 'self::reserveLoginAttempt($this->user)');
$findAt = strpos($login, '$user = $this->find($this->user, $this->password');
$refundAt = strpos($login, 'self::refundLoginAttempt($this->user)');
expect($reserveAt !== false && $findAt !== false && $refundAt !== false, 'login() is not wired to the penalty');
expect($reserveAt > strpos($login, 'if (User::isLogged())') && $reserveAt < $findAt && $findAt < $refundAt, 'login() penalty order changed');
echo "Login failed-attempts regression: passed\n";
