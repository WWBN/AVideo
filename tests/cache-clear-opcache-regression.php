<?php
// Standalone regression: php tests/cache-clear-opcache-regression.php
namespace CacheClearRegression;

$source = file_get_contents(dirname(__DIR__) . '/objects/functions.php');
$start = strpos($source, 'function clearCache($firstPageOnly = false)');
$end = strpos($source, 'function clearAllUsersSessionCache()', $start);
if ($start === false || $end === false) {
    throw new \RuntimeException('Could not locate clearCache');
}
eval('namespace ' . __NAMESPACE__ . ';' . substr($source, $start, $end - $start));

// Isolate data-cache side effects and count opcode resets without touching the
// process-wide OPcache or any actual application cache directory.
function getVideosDir() { return '/fixture/videos/'; }
function file_exists($path) { return false; }
function file_put_contents($path, $value) { return strlen((string) $value); }
function is_dir($path) { return false; }
function rrmdir($path) { $GLOBALS['removed'][] = $path; }
function unlink($path) { return true; }
function _error_log($message) {}
function function_exists($name) { return $name === 'opcache_reset'; }
function class_exists($name) { return false; }
function opcache_reset() { ++$GLOBALS['resets']; return true; }
class ObjectYPT {
    static function getCacheDir($name) { return '/fixture/YPTObjectCache/firstpage/'; }
    static function deleteCache($name) {}
    static function deleteAllSessionCache() {}
}
class AVideoPlugin {
    static function getDataObjectIfEnabled($name) { return false; }
}

$checks = 0;
foreach ([[true, [], 0], [false, ['FirstPage' => 1], 0], [false, [], 1]] as $case) {
    $_REQUEST = $case[1];
    $GLOBALS['resets'] = 0;
    $GLOBALS['removed'] = [];
    if (clearCache($case[0]) !== true || $GLOBALS['resets'] !== $case[2]) {
        throw new \RuntimeException('Unexpected opcode reset for ' . json_encode($case));
    }
    ++$checks;
    $expected = '/fixture/videos/cache' . DIRECTORY_SEPARATOR;
    if (!$case[2]) {
        $expected .= 'firstPage' . DIRECTORY_SEPARATOR;
    }
    if ($GLOBALS['removed'][0] !== $expected || count($GLOBALS['removed']) !== 1) {
        throw new \RuntimeException('Data-cache invalidation changed');
    }
    ++$checks;
}
echo "PASS: $checks cache invalidation checks\n";
