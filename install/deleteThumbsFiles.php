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
        $totalDeleted = Video::deleteThumbs($filename, true, $checkIfIsCorrupted);
        if($totalDeleted){
            echo "{$total}/{$count} Thumbs deleted ($totalDeleted) from {$row['title']}".PHP_EOL;
        }else{
            echo "{$total}/{$count} Thumbs NOT deleted from {$row['title']}".PHP_EOL;
        }
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}