<?php
//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$arrayStatusToDelete = array(
    Video::STATUS_BROKEN_MISSING_FILES,
    Video::STATUS_DOWNLOADING,
    Video::STATUS_ENCODING,
    Video::STATUS_ENCODING_ERROR,
    Video::STATUS_TRANFERING,
);

ob_end_flush();
$sql = "SELECT * FROM  videos where status in ('".implode("', '", $arrayStatusToDelete)."') ";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
$total = count($fullData);
sqlDAL::close($res);
$rows = [];
if ($res != false) {
    $count = 0;
    foreach ($fullData as $key => $row) {
        $count++;
        if(!in_array($row['status'], $arrayStatusToDelete)){
            echo "{$total}/{$count} Deleteuseless skip status={$row['status']} title={$row['title']}".PHP_EOL;
            continue;
        }
        $v = new Video('', '', $row['id']);
        if ($v->delete(true)) {
            echo "{$total}/{$count} Deleteuseless deleted from status={$row['status']} title={$row['title']}".PHP_EOL;
        } else {
            echo "{$total}/{$count} Deleteuseless ERROR  status={$row['status']} title={$row['title']}".PHP_EOL;
        }
    }
}
