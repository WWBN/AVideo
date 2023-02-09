<?php
//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
$checkIfIsCorrupted = intval(@$argv[1]);
echo "checkIfIsCorrupted = $checkIfIsCorrupted" . PHP_EOL;
$users_ids = [];
$sql = "UPDATE videos SET status = ? WHERE status = ? ";
sqlDAL::writeSql($sql, 'ss', array(Video::$statusUnlistedButSearchable,  Video::$statusUnlisted));
