<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface()) {
    die('Command Line only');
}

// Use the same lock and accounting as the automatic ObjectYPT maintenance.
// The old find command raced with writers and left obsolete index files/dirs.
require_once $global['systemRootPath'] . 'objects/ObjectCacheMaintenance.php';
$daysLimit = 3;
$age = isset($global['objectCacheMaxAge']) ? max(60, intval($global['objectCacheMaxAge'])) : ObjectCacheMaintenance::DEFAULT_MAX_AGE;
echo json_encode(ObjectCacheMaintenance::cleanup(ObjectYPT::getTmpCacheDir(), $age)) . PHP_EOL;

//delete site cache
$cacheDir = getCacheDir();
if (empty($cacheDir) || !preg_match('/cache/', $cacheDir)) {
    die('Wrong dir: '.$cacheDir);
}
echo "deleting {$cacheDir}".PHP_EOL;
// delete caches 3 days old
$cmd = "find " . escapeshellarg($cacheDir) . "* -mtime +" . intval($daysLimit) . " -type f -name \"*.cache\" -exec rm {} \;".PHP_EOL;
echo "Command: {$cmd}";
exec($cmd);
