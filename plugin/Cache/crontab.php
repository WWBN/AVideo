<?php

require_once '../../videos/configuration.php';

if (!isCommandLineInterface()) {
    die('Command Line only');
}

$cacheDir = ObjectYPT::getCacheDir();

if(empty($cacheDir) || !preg_match('/YPTObjectCache/', $cacheDir)){
    die('Wrong dir: '.$cacheDir);
}

// delete caches 3 days old
$cmd = "find {$cacheDir}* -mtime +3 -type f -name \"*.cache\" -exec rm {} \;";