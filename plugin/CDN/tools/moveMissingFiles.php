<?php
$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$isCDNEnabled = AVideoPlugin::isEnabledByName('CDN');

if (empty($isCDNEnabled)) {
    return die('Plugin disabled');
}

$alsoMoveUnlisted = intval(@$argv[1]);
// $alsoMoveUnlisted = 1 also unlisted
// $alsoMoveUnlisted = 2 also inactive

$_1hour = 3600;
$_2hours = $_1hour*2;
ob_end_flush();
set_time_limit($_2hours);
ini_set('max_execution_time', $_2hours);

$sort = @$argv[1];
if(empty($sort) || strtolower($sort) !== 'asc'){
    $sort = 'DESC';
}else if(strtolower($sort) == 'asc'){
    $alsoMoveUnlisted = 1;
}

$sql = "SELECT * FROM  videos WHERE 1=1 ORDER BY id $sort ";
$res = sqlDAL::readSql($sql, "", [], true);
$fullData = sqlDAL::fetchAllAssoc($res);
sqlDAL::close($res);

$videos_dir = getVideosDir();
$rows = [];

$transferStatus = [];
$transferStatus[] = Video::$statusActive;
$transferStatus[] = Video::$statusFansOnly;
$transferStatus[] = Video::$statusScheduledReleaseDate;
if($alsoMoveUnlisted){
    $transferStatus[] = Video::$statusUnlisted;
    $transferStatus[] = Video::$statusUnlistedButSearchable;
}

$statusSkipped = array();

if ($res != false) {
    $total = count($fullData);
    foreach ($fullData as $key => $row) {
        $info = "[{$total}, {$key}] ";
        if (in_array($row['status'], $transferStatus) || $alsoMoveUnlisted == 2) {
            //exec("rm {$videos_dir}{$row['filename']}/*.tgz");
            $localList = CDNStorage::getFilesListLocal($row['id'], false);
            $last = end($localList);
            if (empty($last)) {
                echo "{$info} videos_id = {$row['id']} empty local files {$row['status']} {$row['filename']}". PHP_EOL;
                continue;
            }
            if ($last['acumulativeFilesize']<500) {
                echo "{$info} videos_id = {$row['id']} too small size status={$row['status']} acumulativeFilesize={$last['acumulativeFilesize']} ". humanFileSize($last['acumulativeFilesize']). PHP_EOL;
                if($last['acumulativeFilesize']<50){
                   CDNStorage::deleteLog($row['id']);
                }
                //echo "SKIP videos_id = {$row['id']} sites_id is not empty {$row['sites_id']} [{$last['acumulativeFilesize']}] ".humanFileSize($last['acumulativeFilesize']) . PHP_EOL;
            } else {
                if (CDNStorage::isMoving($row['id'])) {
                    echo "{$info} videos_id = {$row['id']} {$row['title']} Is moving ". PHP_EOL;
                } else {
                    echo "{$info} videos_id = {$row['id']} {$row['title']} sites_id is not empty {$row['sites_id']} [{$last['acumulativeFilesize']}] ".humanFileSize($last['acumulativeFilesize']) . PHP_EOL;
                    CDNStorage::put($row['id'], 4);
                    CDNStorage::createDummyFiles($row['id']);
                }
            }
        }else{
            if(!isset($statusSkipped[$row['status']])){
                $statusSkipped[$row['status']] = 0;
            }
            $statusSkipped[$row['status']]++;
            echo "{$info} ERROR skipped status={$row['status']}". PHP_EOL;
        }
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}
echo PHP_EOL . " Done! " . PHP_EOL;


//var_dump($transferStatus);
foreach ($statusSkipped as $key => $value) {
    echo "Skipped {$key}: total={$value}". PHP_EOL;
}
die();
