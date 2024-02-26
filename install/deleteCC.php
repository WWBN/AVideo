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
$users_ids = [];
$sql = "SELECT * FROM  videos ";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
$total = count($fullData);
sqlDAL::close($res);
$rows = [];
if ($res != false) {
    $count = 0;
    foreach ($fullData as $key => $row) {
        $count++;
        $filename = $row['filename'];
        $vtt = "{$global['systemRootPath']}videos/$filename/$filename.vtt";        
        if (file_exists($vtt)) {
            unlink($vtt);
            echo "{$total}/{$count} deleted from {$row['title']}".PHP_EOL;
        } else {
            echo "{$total}/{$count} NOT deleted from {$row['title']} [$vtt]".PHP_EOL;
        }
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}
