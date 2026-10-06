<?php
// Executes the real dispatchers with isolated authentication/rate-limit fixtures.
// No database, live credentials, or server mutation is involved.
class User {
    const TOO_MANY_FAILED_ATTEMPTS = 6;
    public static $logged = false;
    public static $attempts = 0;
    public function __construct($id, $user, $password) {}
    public static function isLogged() { return self::$logged; }
    public function login($noPass, $encoded) { ++self::$attempts; }
}
class ApiObject {}
$source = file_get_contents(dirname(__DIR__) . '/plugin/API/API.php');
$start = strpos($source, '    public function set($parameters)');
$end = strpos($source, '    private function startResponseObject(', $start);
$methods = substr($source, $start, $end - $start);
eval('class DispatcherFixture {' . $methods . '
    public $budget = 0;
    private function checkRateLimit($operation, $limit, $window) {
        if ($operation !== "sign_in" || $limit !== 10 || $window !== 300) {
            throw new RuntimeException("Authentication throttle changed");
        }
        ++$this->budget;
    }
    public function get_api_probe($params) { return true; }
    public function set_api_probe($params) { return true; }
}');
foreach (['get', 'set'] as $method) {
    $api = new DispatcherFixture();
    User::$attempts = 0;
    User::$logged = false;
    $api->$method(['APIName' => 'probe', 'user' => 'fixture', 'pass' => 'fixture']);
    if ($api->budget !== 1 || User::$attempts !== 1) {
        throw new RuntimeException("$method failed to throttle anonymous credentials");
    }
    User::$logged = true;
    for ($i = 0; $i < 25; ++$i) {
        $api->$method(['APIName' => 'probe', 'user' => 'fixture', 'pass' => 'fixture']);
    }
    if ($api->budget !== 1 || User::$attempts !== 1) {
        throw new RuntimeException("$method charged authenticated session requests");
    }
    User::$logged = false;
    $api->$method(['APIName' => 'probe', 'user' => 'fixture', 'pass' => 'wrong']);
    if ($api->budget !== 2 || User::$attempts !== 2) {
        throw new RuntimeException("$method failed to throttle after session expiration");
    }
}
echo "API session rate-limit regression: passed\n";
