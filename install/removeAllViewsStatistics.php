<?php

//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

echo "Start delete statistics" . PHP_EOL;

$sql = "delete FROM videos_statistics where id > 0";
sqlDAL::writeSql($sql);

echo "Finish delete statistics" . PHP_EOL;
