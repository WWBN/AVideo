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
function function_exists($name) { return in_array($name, $GLOBALS['opcodeFunctions'], true); }
function class_exists($name) { return false; }
function opcache_reset() { ++$GLOBALS['resets']; return true; }
function ini_get($name) { return $name === 'opcache.validate_timestamps' ? $GLOBALS['validate'] : false; }
function opcache_get_status($includeScripts) {
    if ($includeScripts !== true) { throw new \RuntimeException('Script listing required'); }
    return $GLOBALS['opcodeStatus'];
}
function opcache_invalidate($file, $force) {
    if ($force !== false) { throw new \RuntimeException('Unchanged bytecode must not be discarded'); }
    $GLOBALS['invalidated'][] = $file;
    return true;
}
class ObjectYPT {
    static function getTmpCacheDir() { return '/fixture/YPTObjectCache/'; }
    static function deleteCache($name) {}
    static function deleteAllSessionCache() {}
}
class AVideoPlugin {
    static function getDataObjectIfEnabled($name) { return false; }
}

$checks = 0;
$global = ['systemRootPath' => '/fixture/site/'];
$GLOBALS['opcodeFunctions'] = ['opcache_reset', 'opcache_get_status', 'opcache_invalidate'];
$GLOBALS['opcodeStatus'] = ['scripts' => [
    '/fixture/site/objects/functions.php' => [],
    '/fixture/site/plugin/Test/Test.php' => [],
    '/fixture/site-other/index.php' => [],
    '/other-site/index.php' => [],
]];
// [firstPageOnly, request, validate_timestamps]. Never restart the pool; a full
// clear must refresh changed PHP even with disabled checks or a high revalidate_freq.
foreach ([
    [true, [], '0'],
    [false, ['FirstPage' => 1], '0'],
    [false, [], '0'],
    [false, [], 'Off'],
    [false, [], ''],
    [false, [], false],
    [false, [], '1'],
    [false, [], 'On'],
] as $case) {
    $_REQUEST = $case[1];
    $GLOBALS['validate'] = $case[2];
    $GLOBALS['resets'] = 0;
    $GLOBALS['removed'] = [];
    $GLOBALS['invalidated'] = [];
    if (clearCache($case[0]) !== true || $GLOBALS['resets'] !== 0) {
        throw new \RuntimeException('Unexpected opcode reset for ' . json_encode($case));
    }
    ++$checks;
    $expected = '/fixture/videos/cache' . DIRECTORY_SEPARATOR;
    if ($case[0] || !empty($case[1]['FirstPage'])) {
        $expected .= 'firstPage' . DIRECTORY_SEPARATOR;
    }
    if ($GLOBALS['removed'][0] !== $expected || count($GLOBALS['removed']) !== 1) {
        throw new \RuntimeException('Data-cache invalidation changed');
    }
    ++$checks;
    $expectedScripts = $case[0] || !empty($case[1]['FirstPage']) ? [] : [
        '/fixture/site/objects/functions.php', '/fixture/site/plugin/Test/Test.php',
    ];
    if ($GLOBALS['invalidated'] !== $expectedScripts) {
        throw new \RuntimeException('PHP refresh or cross-site isolation failed');
    }
    ++$checks;
}

foreach ([false, [], ['scripts' => []]] as $status) {
    $GLOBALS['opcodeStatus'] = $status;
    $GLOBALS['invalidated'] = [];
    $_REQUEST = [];
    clearCache();
    if ($GLOBALS['invalidated'] || $GLOBALS['resets']) { throw new \RuntimeException('Unavailable OPcache'); }
    ++$checks;
}
foreach ([[], ['opcache_reset']] as $functions) {
    $GLOBALS['opcodeFunctions'] = $functions;
    clearCache();
    if ($GLOBALS['resets']) { throw new \RuntimeException('No unsafe reset fallback'); }
    ++$checks;
}
echo "PASS: $checks cache invalidation checks\n";
