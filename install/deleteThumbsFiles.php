<?php

//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
$checkIfIsCorrupted = intval(@$argv[1]);
echo "checkIfIsCorrupted = $checkIfIsCorrupted".PHP_EOL;
$users_ids = array();
$sql = "SELECT * FROM  videos ";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
$total = count($fullData);
sqlDAL::close($res);
$rows = array();
if ($res != false) {
    $count = 0;
    foreach ($fullData as $key => $row) {
        $count++;
        $filename = $row['filename'];
        Video::deleteThumbs($filename, true, $checkIfIsCorrupted);
        echo "{$total}/{$count} Thumbs deleted from {$row['title']}".PHP_EOL;
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}