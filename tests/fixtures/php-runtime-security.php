<?php
// This fixture cannot run through the application's web server. Only the isolated
// temporary loopback router in PHPRuntimeSecurityTest defines this constant.
if (PHP_SAPI !== 'cli' && !defined('AVIDEO_RUNTIME_TEST_SERVER')) {
    http_response_code(404);
    exit;
}
$runtimeTestRoot = dirname(__DIR__, 2);
$runtimeTestInput = PHP_SAPI === 'cli' ? json_decode(stream_get_contents(STDIN), true) : $_GET;
class User
{
    public static $logged = false;
    public static $admin = false;
    public static function isLogged() { return self::$logged; }
    public static function isAdmin() { return self::$admin; }
}
class PluginAbstract {}
function __($text, $allowHTML = false)
{
    global $runtimeTestInput;
    $text = $runtimeTestInput['translations'][$text] ?? $text;
    return $allowHTML ? $text : str_replace(["'", '"', '<', '>'], ['&apos;', '&quot;', '&lt;', '&gt;'], $text);
}
User::$logged = !empty($runtimeTestInput['logged']);
User::$admin = !empty($runtimeTestInput['admin']);
if (isset($runtimeTestInput['script'])) {
    $_SERVER['SCRIPT_NAME'] = $runtimeTestInput['script'];
}
require_once $runtimeTestRoot . '/objects/PHPRuntimeSecurity.php';

switch ($runtimeTestInput['action']) {
    case 'render':
        echo json_encode(PHPRuntimeSecurity::renderAdminNotice($runtimeTestInput['assessment'], !empty($runtimeTestInput['show_passing'])));
        break;
    case 'failure':
        // Simulate a detector failure without changing the real runtime or limits.
        $runtimeTestFailureSource = file_get_contents($runtimeTestRoot . '/objects/PHPRuntimeSecurity.php');
        eval('namespace PHPRuntimeDetectionFailure; function preg_match($pattern, $subject, &$matches = null) { throw new \\RuntimeException("detector unavailable"); } function _error_log($message) { $GLOBALS["runtimeTestFailureLog"] = $message; } ' . substr($runtimeTestFailureSource, strpos($runtimeTestFailureSource, 'class PHPRuntimeSecurity')));
        echo json_encode(['assessment' => \PHPRuntimeDetectionFailure\PHPRuntimeSecurity::detect(), 'log' => $GLOBALS['runtimeTestFailureLog']]);
        break;
    case 'navbar-scroll':
        function TimeLogStart($name) { return $name; }
        function isInfiniteScroll() { return true; }
        ob_start();
        include $runtimeTestRoot . '/view/include/navbar.php';
        echo json_encode(ob_get_clean());
        break;
    case 'cache':
        $runtimeTestCacheSource = file_get_contents($runtimeTestRoot . '/plugin/Cache/Cache.php');
        eval(substr($runtimeTestCacheSource, strpos($runtimeTestCacheSource, 'class Cache extends')));
        $_SERVER['SCRIPT_FILENAME'] = '/site/view/index.php';
        $_GET = [];
        $runtimeTestCache = new Cache();
        $runtimeTestMethod = new ReflectionMethod(Cache::class, 'shouldSkipPageCacheForCurrentRequest');
        $runtimeTestMethod->setAccessible(true);
        $runtimeTestSkip = $runtimeTestMethod->invoke($runtimeTestCache);
        ob_start();
        if ($runtimeTestSkip) {
            // A cache hit must not be read, and getEnd must not write admin HTML.
            $runtimeTestCache->getStart();
            $runtimeTestCache->getEnd();
        }
        $runtimeTestOutput = ob_get_clean();
        echo json_encode(['skip' => $runtimeTestSkip, 'output' => $runtimeTestOutput]);
        break;
    case 'response':
        $runtimeTestTypes = ['html' => 'text/html; charset=UTF-8', 'api' => 'application/json', 'media' => 'video/mp4', 'webhook' => 'text/plain'];
        header('Content-Type: ' . $runtimeTestTypes[$runtimeTestInput['format']]);
        $phpRuntimeSecurityShowPassing = !empty($runtimeTestInput['show_passing']);
        include $runtimeTestRoot . '/view/include/phpRuntimeWarning.php';
        echo $runtimeTestInput['format'] === 'api' ? '{"error":false,"msg":"unchanged"}' : 'UNCHANGED';
        break;
}
