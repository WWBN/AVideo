<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
set_time_limit(300);
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sql = 'DROP TABLE IF EXISTS `CachesInDB`';
$global['mysqli']->query($sql);
$file = $global['systemRootPath'] . 'plugin/Cache/install/install.sql';
sqlDal::executeFile($file);
echo PHP_EOL . " Done! " . PHP_EOL;
die();
