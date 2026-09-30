<?php
// Execute the real set_api_user_delete handler with isolated fixtures; never touch a real account.
class User {
    public static $verifiedId = 0;
    public static $sessionId = 7;
    public static $logged = true;
    public static $deleted = 0;
    public static $loggedOff = 0;
    public function __construct($id, $user = '', $password = '') {}
    public function getUser() { return 'fixture'; }
    public static function getId() { return self::$sessionId; }
    public static function isLogged() { return self::$logged; }
    public static function isAdmin() { return false; }
    public function login($noPass, $encoded) { throw new RuntimeException('Must not trust login() for reauthentication'); }
    public function getVerifiedCredentialsUserId($encoded) { return self::$verifiedId; }
    public function delete() {
        foreach (Video::$rows as $row) {
            if ($row['users_id'] === 7) return false;
        }
        ++self::$deleted;
        return true;
    }
    public static function logoff() { ++self::$loggedOff; }
}
class Permissions {
    public static $canAdminUsers = false;
    public static function canAdminUsers() { return self::$canAdminUsers; }
}
class Video {
    public static $rows = [];
    public static $deleted = 0;
    public static $deleteResult = true;
    public static $cachedRows;
    private $id;
    public function __construct($a, $b, $id) { $this->id = $id; }
    public static function getAllVideosLight($status, $users_id) {
        // sqlDAL memoizes the query for the request; deletes do not invalidate it.
        if (self::$cachedRows === null) self::$cachedRows = array_slice(self::$rows, 0, 2);
        return self::$cachedRows;
    }
    public function getUsers_id() {
        foreach (self::$rows as $row) if ($row['id'] === $this->id) return $row['users_id'];
        return 0;
    }
    public function delete() {
        if (!self::$deleteResult) return false;
        self::$rows = array_values(array_filter(self::$rows, function ($r) { return $r['id'] !== $this->id; }));
        ++self::$deleted;
        return true;
    }
}
class sqlDAL {
    public static $failRead = false;
    public static function readSql($sql, $formats = '', $values = [], $refreshCache = false) {
        if (self::$failRead) return false;
        if (!$refreshCache || $formats !== 'i' || strpos($sql, 'WHERE users_id = ?') === false) {
            throw new RuntimeException('Deletion must use a fresh, parameterized owner query');
        }
        return array_slice(array_values(array_filter(Video::$rows, function ($row) use ($values) {
            return $row['users_id'] === $values[0];
        })), 0, 2);
    }
    public static function fetchAllAssoc($rows) { return $rows; }
    public static function close($rows) {}
}
function forbidIfNotPost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Method not allowed');
}
class ApiObject {
    public $error;
    public $response;
    public $msg;
    public function __construct($message = '', $error = true, $response = null) { $this->msg = $message; $this->error = $error; $this->response = $response; }
}
class Captcha { public static function validation($code) { return $code === 'fixture'; } }
function _error_log($message) {}
$root = sys_get_temp_dir() . '/avideo-user-delete-' . bin2hex(random_bytes(6));
mkdir($root . '/objects', 0700, true);
file_put_contents($root . '/objects/captcha.php', '<?php');
$global = ['systemRootPath' => $root . '/'];
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'POST';
function resetFixtures($rows) {
    User::$deleted = 0; User::$loggedOff = 0; Video::$deleted = 0; Video::$deleteResult = true;
    Video::$rows = $rows;
    Video::$cachedRows = null;
    sqlDAL::$failRead = false;
}
try {
    $source = file_get_contents(dirname(__DIR__) . '/plugin/API/API.php');
    $start = strpos($source, '    public function set_api_user_delete(');
    $end = strpos($source, '    /**
     * Hides comments written by users the current user has blocked', $start);
    if ($start === false || $end === false) throw new RuntimeException('set_api_user_delete not found');
    eval('class DeletionFixture {' . substr($source, $start, $end - $start) . '
        public $budget = 0;
        private function checkRateLimit($op, $limit, $window) { ++$this->budget; }
    }');
    $videos = [['id' => 11, 'users_id' => 7], ['id' => 12, 'users_id' => 7], ['id' => 13, 'users_id' => 7], ['id' => 99, 'users_id' => 8]];
    $params = ['users_id' => 7, 'user' => 'fixture', 'pass' => 'fixture', 'captcha' => 'fixture'];

    // 1. wrong credentials, other account, and matching account
    foreach ([0 => true, 8 => true, 7 => false] as $verifiedId => $expectedError) {
        resetFixtures($videos);
        User::$verifiedId = $verifiedId; User::$sessionId = 7; Permissions::$canAdminUsers = false;
        $api = new DeletionFixture();
        $result = $api->set_api_user_delete($params);
        if ($result->error !== $expectedError || User::$deleted !== ($expectedError ? 0 : 1) || $api->budget !== 1) {
            throw new RuntimeException("Credential/target verification failed for verifiedId=$verifiedId");
        }
        if (!$expectedError) {
            // three own videos removed across paginated rounds, the foreign row skipped, session revoked
            if (Video::$deleted !== 3 || count(Video::$rows) !== 1 || User::$loggedOff !== 1 || $result->response->deleted_videos !== 3) {
                throw new RuntimeException('Own videos must be deleted before the account and the session revoked');
            }
        }
    }

    // 2. session belongs to someone else: refuse even with valid credentials
    resetFixtures($videos);
    User::$verifiedId = 7; User::$sessionId = 9;
    if ((new DeletionFixture())->set_api_user_delete($params)->error !== true || User::$deleted !== 0) {
        throw new RuntimeException('A foreign session must not delete the account');
    }

    // 3. missing captcha / invalid captcha / missing credentials
    resetFixtures($videos);
    User::$verifiedId = 7; User::$sessionId = 7;
    foreach ([['captcha' => ''], ['captcha' => 'wrong'], ['user' => '', 'pass' => '']] as $override) {
        $r = (new DeletionFixture())->set_api_user_delete(array_merge($params, $override));
        if ($r->error !== true || User::$deleted !== 0) throw new RuntimeException('CAPTCHA and credentials are mandatory');
    }

    // 4. a video that cannot be removed stops the deletion
    resetFixtures($videos);
    Video::$deleteResult = false;
    $r = (new DeletionFixture())->set_api_user_delete($params);
    if ($r->error !== true || User::$deleted !== 0 || strpos($r->msg, 'video') === false) {
        throw new RuntimeException('The account must stay when its videos cannot be deleted');
    }

    // 5. admin with the users permission deletes another user without captcha, never themselves
    resetFixtures($videos);
    Permissions::$canAdminUsers = true; User::$sessionId = 1;
    $r = (new DeletionFixture())->set_api_user_delete(['users_id' => 7]);
    if ($r->error !== false || User::$deleted !== 1 || Video::$deleted !== 3 || User::$loggedOff !== 0) {
        throw new RuntimeException('Admin deletion failed');
    }
    resetFixtures($videos);
    User::$sessionId = 7;
    if ((new DeletionFixture())->set_api_user_delete(['users_id' => 7])->error !== true || User::$deleted !== 0) {
        throw new RuntimeException('Admins must not delete themselves');
    }
    // 6. super admin is never deleted through the API
    resetFixtures($videos);
    User::$sessionId = 5;
    if ((new DeletionFixture())->set_api_user_delete(['users_id' => 1])->error !== true || User::$deleted !== 0) {
        throw new RuntimeException('User 1 must be protected');
    }

    // A failed lookup must not be treated as an empty account.
    resetFixtures([]);
    sqlDAL::$failRead = true;
    $r = (new DeletionFixture())->set_api_user_delete(['users_id' => 7]);
    if ($r->error !== true || User::$deleted !== 0) throw new RuntimeException('Failed reads must stop account deletion');

    // Mutating API methods may not be reached through GET, even with credentials/APISecret.
    resetFixtures([]);
    $_SERVER['REQUEST_METHOD'] = 'GET';
    try {
        (new DeletionFixture())->set_api_user_delete(['users_id' => 7]);
        throw new RuntimeException('GET was accepted');
    } catch (RuntimeException $e) {
        if ($e->getMessage() !== 'Method not allowed' || User::$deleted !== 0) throw $e;
    }
    echo "API user delete regression: passed\n";
} finally {
    unlink($root . '/objects/captcha.php'); rmdir($root . '/objects'); rmdir($root);
}
