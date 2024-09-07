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
$startFromIndex = intval(@$argv[1]);
$_1hour = 3600;
$_2hours = $_1hour * 2;
ob_end_flush();
set_time_limit($_2hours);
ini_set('max_execution_time', $_2hours);
$parts = explode('.', $cdnObj->storage_hostname);
$apiAccessKey = $cdnObj->storage_password;
$storageZoneName = $cdnObj->storage_username;
$storageZoneRegion = trim(strtolower($parts[0]));

echo ("CDNStorage::APIput line $apiAccessKey, $storageZoneName, $storageZoneRegion [startFromIndex=$startFromIndex]") . PHP_EOL;
$client = new \Bunny\Storage\Client($apiAccessKey, $storageZoneName, $storageZoneRegion);

$sql = "SELECT * FROM videos ORDER BY id";
$res = sqlDAL::readSql($sql, "", [], true);
$fullData = sqlDAL::fetchAllAssoc($res);
sqlDAL::close($res);

$secondsInAMinute = 60;
$secondsInAnHour = 60 * $secondsInAMinute;
$secondsInADay = 24 * $secondsInAnHour;
$secondsInAWeek = 7 * $secondsInADay;
$secondsInAMonth = 30 * $secondsInADay;

$totalProcessedTime = 0;
$processedFilesCount = 0;

$totalSpeed = 0;

if ($res != false) {
    $total = count($fullData);
    echo ("CDNStorage::APIput found {$total} videos") . PHP_EOL;
    foreach ($fullData as $key => $row) {
        if ($key < $startFromIndex) {
            continue;
        }
        $videos_id = $row['id'];
        $info1 = "videos_id = $videos_id [{$total}, {$key}] ";

        // I want to use the $localList var below to speed up the process
        $localList = CDNStorage::getFilesListLocal($videos_id, false);
        $validFound = false;
        foreach ($localList as $value) {
            $filesize = filesize($value['local_path']);
            if($filesize > 20){
                echo ("{$info1} CDNStorage::APIput valid file found {$value['local_path']}") . PHP_EOL;
                $validFound = true;
                break;
            }
        }
        if(!$validFound){
            echo ("{$info1} CDNStorage::APIput local_path filesize is invalid ") . PHP_EOL;
            continue;
        }

        $list = CDNStorage::getFilesListBoth($videos_id);
        $totalFiles = count($list);
        echo ("{$info1} CDNStorage::APIput found {$totalFiles} files for videos_id = $videos_id ") . PHP_EOL;
        $countFiles = 0;
        foreach ($list as $value) {
            $countFiles++;
            $info2 = "{$info1}[{$totalFiles}, {$countFiles}] ";
            if (empty($value['local'])) {
                continue;
            }
            $filesize = filesize($value['local']['local_path']);
            if ($value['isLocal'] && $filesize > 20) {
                if (empty($value) || empty($value['remote']) || $filesize != $value['remote']['remote_filesize']) {
                    $remote_file = CDNStorage::filenameToRemotePath($value['local']['local_path']);
                    $startTime = microtime(true);
                    echo PHP_EOL . ("$info2 {$remote_file} " . humanFileSize($filesize)) . PHP_EOL;
                    try {
                        $client->upload($value['local']['local_path'], $remote_file);
                    } catch (\Throwable $th) {
                        echo "$info2 CDNStorage::APIput Upload ERROR " . $th->getMessage() . PHP_EOL;
                    }
                    $endTime = microtime(true);

                    $timeTaken = $endTime - $startTime;
                    $speed = $filesize/$timeTaken;
                    $totalProcessedTime += $timeTaken;
                    $processedFilesCount++;

                    $totalSpeed+= $speed;
                    $averageSpeed = $totalSpeed / $processedFilesCount;

                    // Average time per video
                    $averageTimePerFile = $totalProcessedTime / $processedFilesCount;
                    $remainingFilesInThisVideo = $totalFiles - $countFiles;
                    $etaForThisVideo = $averageTimePerFile * $remainingFilesInThisVideo;

                    $remainingVideos = $total - $key;
                    $etaForAllVideos = ($averageTimePerFile * $totalFiles) * $remainingVideos;

                    // Convert the estimated time into a readable format
                    $months = floor($etaForAllVideos / $secondsInAMonth);
                    $weekSeconds = (int)$etaForAllVideos % (int)$secondsInAMonth;
                    $weeks = floor($weekSeconds / $secondsInAWeek);
                    $daySeconds = (int)$weekSeconds % (int)$secondsInAWeek;
                    $days = floor($daySeconds / $secondsInADay);
                    $hourSeconds = (int)$daySeconds % (int)$secondsInADay;
                    $hours = floor($hourSeconds / $secondsInAnHour);
                    $minuteSeconds = (int)$hourSeconds % (int)$secondsInAnHour;
                    $minutes = floor($minuteSeconds / $secondsInAMinute);
                    $remainingSeconds = (int)$minuteSeconds % $secondsInAMinute;

                    $thisFile = humanFileSize($speed) . "/s average: ".humanFileSize($averageSpeed) . "/s ".@gmdate("H:i:s", $etaForThisVideo);
                    $ETAAllVideos = "{$months}m {$weeks}w {$days}d {$hours}:{$minutes}:{$remainingSeconds} ";
                    echo "$info2 $thisFile ALL: $ETAAllVideos" . PHP_EOL;
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

die();
