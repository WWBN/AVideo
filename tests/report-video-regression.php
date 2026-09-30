<?php
// Execute production plugin methods/endpoints without loading a site's configuration or database.
class PluginAbstract {}
class User {
    public static $logged = true;
    private $id;
    public function __construct($id, $user = '', $pass = '') { $this->id = $id; }
    public function getUser() { return $this->id === 404 ? '' : 'fixture'; }
    public static function getId() { return self::$logged ? 7 : 0; }
    public static function isLogged() { return self::$logged; }
    public static function loginFromRequestIfNotLogged() {
        if (self::$logged) return;
        inputToRequest();
        self::$logged = ($_REQUEST['user'] ?? '') === 'fixture' && ($_REQUEST['pass'] ?? '') === 'fixture';
    }
}
class VideosReported {
    public static $rows = [];
    private $id;
    private $owner;
    private $target;
    public function __construct($id) { $this->id = $id; }
    public static function getFromDbUserAndReportedUser($owner, $target) {
        foreach (self::$rows as $row) if ($row['users_id'] === $owner && $row['reported_users_id'] === $target) return $row;
        return [];
    }
    public static function getAllReportedUsersIdFromUser($owner) { return array_column(self::$rows, 'reported_users_id'); }
    public function setUsers_id($id) { $this->owner = $id; }
    public function setReported_users_id($id) { $this->target = $id; }
    public function save() {
        self::$rows[1] = ['id' => 1, 'users_id' => $this->owner, 'reported_users_id' => $this->target];
        return 1;
    }
    public function delete() { unset(self::$rows[$this->id]); return true; }
}
class VideosListCacheHandler {
    public static $cached = ['old video list'];
    public function deleteCache($clearFirstPageCache = false, $schedule = true) {
        if (!$schedule) self::$cached = [];
    }
}
class AVideoPlugin {
    public static $enabled = true;
    public static function loadPluginIfEnabled($name) { return self::$enabled ? new ReportVideo() : null; }
}
class Comment {
    public static function getComment($id) { return $id === 12 ? ['videos_id' => 91] : false; }
}
function __($text) { return $text; }
function _error_log($message) {}
function inputToRequest() {
    global $scenario;
    foreach ($scenario['body'] ?? [] as $key => $value) {
        if (!isset($_REQUEST[$key])) $_REQUEST[$key] = $value;
    }
}
function forbiddenPage($message, $log = false) {
    die(json_encode(['error' => true, 'msg' => $message, 'forbiddenPage' => true]));
}
function forbiddenPageIfCannotWatchVideo($id) {
    global $scenario;
    if (!empty($scenario['hidden'])) forbiddenPage('Cannot watch video');
}
function sendEmailToSiteOwner($subject, $message) {
    global $scenario;
    if (!empty($scenario['throwMail'])) throw new RuntimeException('Private SMTP configuration');
    return !empty($scenario['mailSent']);
}

$root = dirname(__DIR__);
$source = file_get_contents($root . '/plugin/ReportVideo/ReportVideo.php');
eval(substr($source, strpos($source, 'class ReportVideo extends')));
$source = file_get_contents($root . '/objects/functionsSecurity.php');
$start = strpos($source, 'function forbidIfNotPost(');
$end = strpos($source, 'function forbidIfInvalidToken(', $start);
eval(substr($source, $start, $end - $start));

$scenario = json_decode(base64_decode($argv[1]), true);
if (($scenario['endpoint'] ?? '') === 'methods') {
    $plugin = new ReportVideo();
    $response = $plugin->block(7, 9);
    if ($response->error || VideosListCacheHandler::$cached !== []) throw new RuntimeException('Block left cached videos visible');
    VideosListCacheHandler::$cached = ['old blocked list'];
    $response = $plugin->unBlock(7, 9);
    if ($response->error || VideosListCacheHandler::$cached !== []) throw new RuntimeException('Unblock left stale cached lists');
    if (ReportVideo::sanitizeReportReason(['invalid']) !== '' || ReportVideo::sanitizeReportReason((object) []) !== '') {
        throw new RuntimeException('Invalid reason type was accepted');
    }
    if (ReportVideo::sanitizeReportReason("  <b>Test</b>\n reason ") !== 'Test reason' || mb_strlen(ReportVideo::sanitizeReportReason(str_repeat('é', 300))) !== 255) {
        throw new RuntimeException('Report reason sanitization failed');
    }

    $source = file_get_contents($root . '/plugin/API/API.php');
    $start = strpos($source, '    private static function removeBlockedUsersComments(');
    $end = strpos($source, '    /**', $start);
    eval('class CommentFilterFixture {' . substr($source, $start, $end - $start) . ' public static function filter($rows) { return self::removeBlockedUsersComments($rows); }}');
    $plugin->block(7, 9);
    $rows = [
        ['id' => 1, 'users_id' => 9],
        ['id' => 2, 'users_id' => 8, 'responses' => [['id' => 3, 'users_id' => 9], ['id' => 4, 'users_id' => 8]]],
    ];
    $filtered = CommentFilterFixture::filter($rows);
    if (array_column($filtered, 'id') !== [2] || array_column($filtered[0]['responses'], 'id') !== [4]) {
        throw new RuntimeException('Blocked authors remain in comments/replies');
    }
    AVideoPlugin::$enabled = false;
    if (CommentFilterFixture::filter($rows) !== $rows) throw new RuntimeException('Disabled plugin changed comments');
    echo json_encode(['error' => false]);
    exit;
}

$_REQUEST = $scenario['form'] ?? [];
$_GET = [];
$_SERVER['REQUEST_METHOD'] = $scenario['method'] ?? 'POST';
User::$logged = $scenario['logged'] ?? true;
$endpoint = $scenario['endpoint'] === 'block' ? 'block' : 'report';
$source = file_get_contents($root . '/plugin/ReportVideo/' . $endpoint . '.json.php');
// Isolate only configuration/class imports; execute all production guards and action branches.
$source = preg_replace('/^\s*require_once .*;\s*$/m', '', $source);
eval(substr($source, 5));
