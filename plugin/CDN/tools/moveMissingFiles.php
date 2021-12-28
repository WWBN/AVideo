<?php

$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$isCDNEnabled = AVideoPlugin::isEnabledByName('CDN');

if (empty($isCDNEnabled)) {
    return die('Plugin disabled');
}


ob_end_flush();
set_time_limit(1800);
ini_set('max_execution_time', 1800);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$sql = "SELECT * FROM  videos WHERE 1=1 ORDER BY id DESC ";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
sqlDAL::close($res);
$rows = array();
if ($res != false) {
    foreach ($fullData as $row) {
        if (!empty($row['sites_id'])) {
            $localList = CDNStorage::getFilesListLocal($row['id'], false);
            $last = end($localList);
            if(empty($last)){
                continue;
            }
            if($last['acumulativeFilesize']<10000){
                //echo "SKIP videos_id = {$row['id']} sites_id is not empty {$row['sites_id']} [{$last['acumulativeFilesize']}] ".humanFileSize($last['acumulativeFilesize']) . PHP_EOL;
            }else{
                echo "videos_id = {$row['id']} {$row['title']} sites_id is not empty {$row['sites_id']} [{$last['acumulativeFilesize']}] ".humanFileSize($last['acumulativeFilesize']) . PHP_EOL;
                CDNStorage::put($row['id'], 4);
                //CDNStorage::createDummyFiles($row['id']);
            }
        }
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}
echo PHP_EOL . " Done! " . PHP_EOL;
die();

