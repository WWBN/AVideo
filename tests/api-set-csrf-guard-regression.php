<?php
// Executes the real CSRF guard from plugin/API/set.json.php with isolated fixtures.
// No database, live credentials, or server mutation is involved.
class User {
    public static $logged = false;
    public static function isLogged() { return self::$logged; }
}
class API {
    public static $secretValid = false;
    public static function isAPISecretValid() { return self::$secretValid; }
}
class GuardStop extends Exception {}
function requestComesFromSameDomainAsMyAVideo() { return !empty($GLOBALS['fixtureSameOrigin']); }
function isAVideoUserAgent() { return false; }
function getRealIpAddr() { return '203.0.113.7'; }
function guardStop($json) { throw new GuardStop(json_decode($json, true)['msg']); }

$root = dirname(__DIR__);
$source = file_get_contents($root . '/plugin/API/set.json.php');
$start = strpos($source, '$ambientlyLoggedIn =');
$end = strpos($source, '$obj = $plugin->set(', $start);
if ($start === false || $end === false) {
    throw new RuntimeException('set.json.php guard not found');
}
$guard = str_replace('die(', 'guardStop(', substr($source, $start, $end - $start));

function runGuard($guard, $method, $parameters, $logged, $fromRequest, $sameOrigin = false, $secret = false)
{
    global $global;
    $global = $fromRequest ? ['loggedInFromRequestCredentials' => 1] : [];
    $GLOBALS['fixtureSameOrigin'] = $sameOrigin;
    $_SERVER['REQUEST_METHOD'] = $method;
    User::$logged = $logged;
    API::$secretValid = $secret;
    try {
        eval($guard);
    } catch (GuardStop $e) {
        return $e->getMessage();
    }
    return 'allowed';
}

$creds = ['APIName' => 'like', 'user' => 'fixture', 'pass' => 'fixture'];
$cases = [
    // session created by this request's own user/pass during bootstrap (plugins' getStart)
    'request credentials, cross-site POST' => [runGuard($guard, 'POST', $creds, true, true), 'allowed'],
    // credentials sent but wrong: set() still runs and reports the failed login
    'failed credentials, cross-site POST' => [runGuard($guard, 'POST', $creds, false, false), 'allowed'],
    'APISecret, cross-site POST' => [runGuard($guard, 'POST', ['APIName' => 'like'], true, false, false, true), 'allowed'],
    'ambient session, same-origin POST' => [runGuard($guard, 'POST', ['APIName' => 'like'], true, false, true), 'allowed'],
    // GHSA-mwr9-5m78-rhgm: victim cookie + forged user/pass must stay blocked
    'ambient session + forged credentials, cross-site POST' => [runGuard($guard, 'POST', $creds, true, false), 'Invalid Request 203.0.113.7'],
    'ambient session + forged credentials, cross-site GET' => [runGuard($guard, 'GET', $creds, true, false), 'Method not allowed'],
    'no credentials, cross-site POST' => [runGuard($guard, 'POST', ['APIName' => 'like'], false, false), 'Invalid Request 203.0.113.7'],
];
foreach ($cases as $name => [$actual, $expected]) {
    if ($actual !== $expected) {
        throw new RuntimeException("$name: expected '$expected', got '$actual'");
    }
}

// loginFromRequest() may only raise the flag right under one of these two password-checking guards:
// the pre-existing session (cookie) branch, or the fresh login. USER_LOGGED is 0, so anything inside
// the later "if ($response) { switch" never runs on success.
$userSource = file_get_contents($root . '/objects/user.php');
$fnStart = strpos($userSource, 'public static function loginFromRequest()');
$fnEnd = strpos($userSource, 'public static function loginFromRequestToGet()', $fnStart);
$fn = substr($userSource, $fnStart, $fnEnd - $fnStart);
$flag = "\$global['loggedInFromRequestCredentials'] = 1;";
$earlyReturn = strpos($fn, 'return self::USER_LOGGED;');
$freshLogin = strpos($fn, '$response = $user->login(');
$allowedGuards = [
    'if ($requestUsersId === (int) $_SESSION[\'user\'][\'id\']) {',
    'if ($response === self::USER_LOGGED && self::$loginCheckedPassword) {',
];
$lines = array_values(array_filter(array_map('trim', explode("\n", $fn)), function ($line) {
    return $line !== '' && strpos($line, '//') !== 0;
}));
$guardsSeen = [];
foreach ($lines as $i => $line) {
    if ($line === $flag) {
        if (!in_array($lines[$i - 1], $allowedGuards, true)) {
            throw new RuntimeException('request-credentials flag raised without a password check: ' . $lines[$i - 1]);
        }
        $guardsSeen[] = $lines[$i - 1];
    }
}
if ($guardsSeen !== $allowedGuards || strpos($fn, $allowedGuards[0]) > $earlyReturn
    || strpos($fn, $allowedGuards[1]) < $freshLogin || strpos($fn, $allowedGuards[1]) > strpos($fn, 'if ($response) {')) {
    throw new RuntimeException('loginFromRequest() request-credentials flag guards moved');
}
// switching away from the cookie session is opt-in (API entry points only) and needs verified credentials
$switchAt = strpos($fn, "if (!empty(\$global['switchUserFromRequestCredentials'])) {");
$logoffAt = strpos($fn, 'self::logoff();');
if ($switchAt === false || $logoffAt < $switchAt || substr_count($fn, 'self::logoff();') !== 1
    || strpos($fn, '$requestUsersId && $requestUsersId !== (int) $_SESSION[\'user\'][\'id\']') === false) {
    throw new RuntimeException('loginFromRequest() account switch is no longer gated');
}
foreach (['get.json.php', 'set.json.php'] as $entry) {
    $entrySource = file_get_contents($root . "/plugin/API/$entry");
    $optIn = strpos($entrySource, "\$global['switchUserFromRequestCredentials'] = 1;");
    if ($optIn === false || $optIn > strpos($entrySource, 'require_once $configFile;')) {
        throw new RuntimeException("$entry must opt in to the account switch before loading configuration");
    }
}
$login = substr($userSource, strpos($userSource, '    public function login('), 4000);
if (strpos($login, 'self::$loginCheckedPassword = true;') < strpos($login, '$user = $this->find($this->user, $this->password')) {
    throw new RuntimeException('login() must mark loginCheckedPassword only after the password check');
}

// Run the real getRequestCredentialsUsersId() against fixed credentials
$start = strpos($userSource, '    private static function getRequestCredentialsUsersId()');
$end = strpos($userSource, '    public static function isCaptchaNeed()', $start);
class CredentialUser {
    public static $checks = 0;
    private $user;
    private $pass;
    public function __construct($id, $user, $pass) { $this->user = $user; $this->pass = $pass; }
    public function getVerifiedCredentialsUserId($encodedPass = false) {
        ++self::$checks;
        $accounts = ['admin' => [1, '123'], 'other' => [2, 'abc']];
        return isset($accounts[$this->user]) && $accounts[$this->user][1] === $this->pass ? $accounts[$this->user][0] : 0;
    }
}
eval('class SessionCredentialFixture {
    public static $reserved = 0;
    public static $refunded = 0;
    private static function reserveLoginAttempt($user) { ++self::$reserved; return true; }
    private static function refundLoginAttempt($user) { ++self::$refunded; }
    public static function check() { return self::getRequestCredentialsUsersId(); }
' . str_replace('new User(', 'new CredentialUser(', substr($userSource, $start, $end - $start)) . '}');

function credentialsCase($user, $pass, $switch)
{
    global $global;
    $global = $switch ? ['switchUserFromRequestCredentials' => 1] : [];
    $_SESSION['user'] = ['id' => 1, 'user' => 'admin', 'email' => 'admin@example.test'];
    $_REQUEST = ['user' => $user, 'pass' => $pass];
    CredentialUser::$checks = 0;
    $users_id = SessionCredentialFixture::check();
    return [$users_id, CredentialUser::$checks];
}
$credentialCases = [
    'session user, right password' => [credentialsCase('admin', '123', false), [1, 1]],
    'session user by email is still the session user' => [credentialsCase('ADMIN@example.test', 'x', false), [0, 1]],
    'session user, wrong password' => [credentialsCase('admin', 'nope', false), [0, 1]],
    // normal pages: another account's credentials are not even checked, the session stays
    'other user on a page' => [credentialsCase('other', 'abc', false), [0, 0]],
    'other user on the API, right password' => [credentialsCase('other', 'abc', true), [2, 1]],
    'other user on the API, wrong password' => [credentialsCase('other', 'nope', true), [0, 1]],
];
foreach ($credentialCases as $name => [$actual, $expected]) {
    if ($actual !== $expected) {
        throw new RuntimeException("$name: expected [users_id, checks] " . json_encode($expected) . ", got " . json_encode($actual));
    }
}
echo "API set CSRF guard regression: passed\n";
