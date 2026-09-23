<?php
// Standalone endpoint regression: php tests/encoder-completion-regression.php
if (($argv[1] ?? '') === 'request') {
    $root = $argv[2];
    $scenario = $argv[3];
    $global = ['systemRootPath' => $root . '/'];
    $_REQUEST = ['videos_id' => 71, 'pass' => 'SECRET'];
    function allowOrigin() {}
    function inputToRequest() {}
    function useVideoHashOrLogin() {}
    function __($message) { return $message; }
    function _error_log($message) {}
    function getTmpDir($name) { return $GLOBALS['root'] . '/'; }
    class User {
        static function canUpload() { return $GLOBALS['scenario'] !== 'denied'; }
    }
    class Video {
        const STATUS_ACTIVE = 'a';
        function __construct(...$args) {}
        static function canEdit($id) { return $GLOBALS['scenario'] !== 'other-owner'; }
        static function clearCache(...$args) {}
        static function updateFilesize($id) {}
        static function getStoragePath() { return $GLOBALS['root'] . '/'; }
        function getFilename() { return 'test'; }
        function getTitle() { return 'Test video'; }
        function setAutoStatus($status) {}
        function save() { return $GLOBALS['scenario'] === 'save-fails' ? false : 71; }
    }
    class AVideoPlugin {
        static function onEncoderNotifyIsDone($id) {
            file_put_contents($GLOBALS['root'] . '/calls', 'call\n', FILE_APPEND);
            if ($GLOBALS['scenario'] === 'hook-fails') {
                throw new RuntimeException('Simulated plugin failure');
            }
        }
        static function afterNewVideo($id) {}
    }
    require dirname(__DIR__) . '/objects/aVideoEncoderNotifyIsDone.json.php';
    exit;
}

$root = sys_get_temp_dir() . '/avideo-completion-' . bin2hex(random_bytes(8));
mkdir($root . '/objects', 0700, true);
file_put_contents($root . '/objects/user.php', '<?php');
file_put_contents($root . '/objects/video.php', '<?php');
$checks = 0;
function check($condition, $message) {
    global $checks;
    if (!$condition) { throw new RuntimeException($message); }
    ++$checks;
}
function requestCompletion($scenario) {
    global $root;
    $command = [PHP_BINARY, __FILE__, 'request', $root, $scenario];
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    check(proc_close($process) === 0, 'Request failed: ' . $errors);
    $result = json_decode($output, true);
    check(is_array($result), 'Expected a JSON response');
    return $result;
}
try {
    $marker = $root . '/video_71';
    $failure = requestCompletion('hook-fails');
    check($failure['error'] && $failure['code'] === 'completion_failed', 'Plugin failure must have a stable code');
    check(!file_exists($marker), 'Failure must not create a completion marker');
    check(!requestCompletion('ok')['error'], 'Retry must complete');
    check(json_decode(file_get_contents($marker), true)['completed'], 'Success must be recorded');
    $calls = file_get_contents($root . '/calls');
    check(!requestCompletion('ok')['error'], 'Repeated completion must succeed');
    check(file_get_contents($root . '/calls') === $calls, 'Completed retry must not rerun hooks');
    $denied = requestCompletion('denied');
    check($denied['error'] && $denied['code'] === 'streamer_access_denied', 'Existing marker must not bypass authentication');
    check(strpos(json_encode($denied), 'SECRET') === false, 'Access rejection must not echo credentials');
    $denied = requestCompletion('other-owner');
    check($denied['error'] && $denied['code'] === 'destination_unavailable', 'Existing marker must not bypass ownership');
    check(strpos(json_encode($denied), 'SECRET') === false, 'Ownership rejection must not echo credentials');
    file_put_contents($marker, (string) time());
    check(!requestCompletion('ok')['error'], 'Legacy premature marker must allow recovery');
    unlink($marker);
    check(requestCompletion('save-fails')['error'], 'Failed save must be reported');
    check(!file_exists($marker), 'Failed save must not create a marker');
    $lock = fopen($marker . '.lock', 'c');
    flock($lock, LOCK_EX);
    $busy = requestCompletion('ok');
    check($busy['error'] && $busy['code'] === 'completion_in_progress', 'Concurrent request must explain that processing continues');
    flock($lock, LOCK_UN); fclose($lock);
    check(!requestCompletion('ok')['error'], 'Retry after lock release must complete');
    echo "PASS: {$checks} completion checks\n";
} finally {
    foreach (glob($root . '/objects/*') as $file) { unlink($file); }
    rmdir($root . '/objects');
    foreach (glob($root . '/*') as $file) { unlink($file); }
    rmdir($root);
}
