<?php
$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$cdnObj = AVideoPlugin::getDataObjectIfEnabled('CDN');

if (empty($cdnObj)) {
    return die('Plugin disabled');
}

$_1hour = 3600;
$_2hours = $_1hour * 2;
ob_end_flush();
set_time_limit($_2hours);
ini_set('max_execution_time', $_2hours);
$parts = explode('.', $cdnObj->storage_hostname);
$apiAccessKey = $cdnObj->storage_password;
$storageZoneName = $cdnObj->storage_username; // Replace with your storage zone name
$storageZoneRegion = trim(strtoupper($parts[0])); // Replace with your storage zone region code

echo ("CDNStorage::APIput line $apiAccessKey, $storageZoneName, $storageZoneRegion ") . PHP_EOL;
$client = new \Bunny\Storage\Client($apiAccessKey, $storageZoneName, $storageZoneRegion);
echo ("CDNStorage::APIput line " . __LINE__) . PHP_EOL;

$sql = "SELECT * FROM  videos WHERE 1=1 ORDER BY id ";
$res = sqlDAL::readSql($sql, "", [], true);
echo ("CDNStorage::APIput line " . __LINE__) . PHP_EOL;
$fullData = sqlDAL::fetchAllAssoc($res);
echo ("CDNStorage::APIput line " . __LINE__) . PHP_EOL;
sqlDAL::close($res);
echo ("CDNStorage::APIput line " . __LINE__) . PHP_EOL;

if ($res != false) {
    $total = count($fullData);
    echo ("CDNStorage::APIput found {$total} videos") . PHP_EOL;
    foreach ($fullData as $key => $row) {
        $info1 = "videos_id = $videos_id [{$total}, {$key}] ";
        $videos_id = $row['id'];
        $list = CDNStorage::getFilesListBoth($videos_id);
        $totalFiles = count($list);
        echo ("{$info1} CDNStorage::APIput found {$totalFiles} files for videos_id = $videos_id ") . PHP_EOL;
        $count = 0;
        $totalSizeRemaining = array_sum(array_map(function ($value) {
            return $value['isLocal'] ? filesize($value['local']['local_path']) : 0;
        }, $list));

        foreach ($list as $value) {
            $count++;
            $info2 = "{$info1}[{$totalFiles}, {$count}] ";
            if (empty($value['local'])) {
                continue;
            }
            $filesize = filesize($value['local']['local_path']);
            if ($value['isLocal'] && $filesize > 20) {
                if (empty($value) || empty($value['remote']) || $filesize != $value['remote']['remote_filesize']) {
                    $remote_file = CDNStorage::filenameToRemotePath($value['local']['local_path']);
                    $startTime = microtime(true);
                    echo PHP_EOL . ("$info2 CDNStorage::APIput {$value['local']['local_path']} {$remote_file} " . humanFileSize($filesize)) . PHP_EOL;
                    try {
                        $client->upload($value['local']['local_path'], $remote_file);
                    } catch (\Throwable $th) {
                        echo "$info2 CDNStorage::APIput Upload ERROR " .$th->getMessage() . PHP_EOL;
                    }
                    $endTime = microtime(true);
                    $timeTaken = $endTime - $startTime; // Time taken in seconds
                    $totalSizeRemaining -= $filesize; // Update remaining size
                    $timeTakenFormated = number_format($timeTaken, 1);
                    $speed = $filesize / $timeTaken; // Bytes per second
                    $etaForCurrentFile = $totalSizeRemaining / $speed; // ETA in seconds
                    $totalTimeEstimated = $etaForCurrentFile * ($total - $key);
                    echo "$info2 CDNStorage::APIput Upload complete. $timeTakenFormated seconds, Speed: " . humanFileSize($speed) . "/s, files ETA: " . @gmdate("H:i:s", $etaForCurrentFile) . " Videos ETA: " . @gmdate("d H:i:s", $totalTimeEstimated) . PHP_EOL;
                } else {
                    echo ("$info2 CDNStorage::APIput same size {$value['remote']['remote_filesize']} {$value['remote']['relative']}") . PHP_EOL;
                }
            } else {
                echo ("{$info1} CDNStorage::APIput not valid local file {$value['local']['local_path']}") . PHP_EOL;
            }
        }
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}
echo PHP_EOL . " Done! " . PHP_EOL;


//var_dump($transferStatus);
foreach ($statusSkipped as $key => $value) {
    echo "Skipped {$key}: total={$value}" . PHP_EOL;
}
die();
