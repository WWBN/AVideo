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
$apiAccessKey = readline("Enter BunnyCDN Storage API Access Key: ");
$storageZoneName = $cdnObj->storage_username; // Replace with your storage zone name
$storageZoneRegion = $parts[0]; // Replace with your storage zone region code

$client = new \Bunny\Storage\Client($apiAccessKey, $storageZoneName, $storageZoneRegion);

$sql = "SELECT * FROM  videos WHERE 1=1 ORDER BY id ";
$res = sqlDAL::readSql($sql, "", [], true);
$fullData = sqlDAL::fetchAllAssoc($res);
sqlDAL::close($res);

if ($res != false) {
    $total = count($fullData);
    _error_log("CDNStorage::put found {$total}");
    foreach ($fullData as $key => $row) {
        $info = "[{$total}, {$key}] ";
        $videos_id = $row['id'];
        $list = CDNStorage::getFilesListBoth($videos_id);
        $totalFiles = count($list);
        _error_log("CDNStorage::put found {$totalFiles} files for videos_id = $videos_id ");
        foreach ($list as $value) {
            if (empty($value['local'])) {
                continue;
            }
            $filesize = filesize($value['local']['local_path']);
            if ($value['isLocal'] && $filesize > 20) {
                if (empty($value) || empty($value['remote']) || $filesize != $value['remote']['remote_filesize']) {
                    $remote_file = CDNStorage::filenameToRemotePath($value['local']['local_path']);
                    _error_log("CDNStorage::put {$value['local']['local_path']} {$remote_file} {$value['remote']['relative']}");
                    $client->upload($value['local']['local_path'], $remote_file);
                } else {
                    _error_log("CDNStorage::put same size {$value['remote']['remote_filesize']} {$value['remote']['relative']}");
                }
            } else {
                _error_log("CDNStorage::put not valid local file {$value['local']['local_path']}");
            }
            exit;
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
