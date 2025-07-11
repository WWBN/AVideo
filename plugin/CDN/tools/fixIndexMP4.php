<?php
$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$global['printLogs'] = 1;
$global['debug'] = $_REQUEST['debug'] = 1;
error_reporting(E_ALL);
ini_set('display_errors', 1);

$isCDNEnabled = AVideoPlugin::isEnabledByName('CDN');

if (empty($isCDNEnabled)) {
    return die('Plugin disabled');
}

$index = intval(@$argv[1]);

$path = getVideosDir();
ob_end_flush();
set_time_limit(300);
ini_set('max_execution_time', 300);

$tenMB = 10240000;

$sql = "SELECT * FROM  videos WHERE 1=1 ORDER BY id DESC ";
$res = sqlDAL::readSql($sql);
$videos = sqlDAL::fetchAllAssoc($res);
$total = count($videos);
$client = CDNStorage::getStorageClient();
sqlDAL::close($res);

echo "Total videos found: {$total}" . PHP_EOL;

foreach ($videos as $key => $value) {
    $videos_id = $value['id'];
    $filename = $value['filename'];

    if ($value['status'] === Video::STATUS_ACTIVE) {
        echo "[videos_id: {$videos_id}] Processing '{$filename}'" . PHP_EOL;

        if (empty($value['sites_id'])) {
            echo "[videos_id: {$videos_id}] ➤ Skipped: No sites_id" . PHP_EOL;
            continue;
        }

        $hls = "{$path}{$filename}/index.m3u8";
        $mp4 = "{$path}{$filename}/index.mp4";

        if (file_exists($hls) && file_exists($mp4) && isDummyFile($mp4)) {
            echo "[videos_id: {$videos_id}] ➤ Found dummy MP4 and valid HLS" . PHP_EOL;

            $fileInfo = CDNStorage::getFilesListInfo($mp4, $videos_id);
            $filesize = $fileInfo['local_filesize'] ?? 0;
            echo "[videos_id: {$videos_id}]     - MP4 Dummy Size: {$filesize} bytes" . PHP_EOL;

            if ($filesize < 20) {
                echo "[videos_id: {$videos_id}]     - Starting HLS to MP4 conversion..." . PHP_EOL;
                $resp = VideoHLS::convertM3U8ToMP4AndSync($videos_id, 1);
                echo "[videos_id: {$videos_id}]     - Conversion complete. Result: " . json_encode($resp) . PHP_EOL;
            } else {
                echo "[videos_id: {$videos_id}]     - File is not dummy (>= 20 bytes), skipping conversion." . PHP_EOL;
            }
        } else {
            echo "[videos_id: {$videos_id}] ➤ Skipped: HLS or MP4 file missing, or MP4 is not dummy" . PHP_EOL;
        }
    } else {
        echo "[videos_id: {$videos_id}] ➤ Skipped: Video not active" . PHP_EOL;
    }
}

echo PHP_EOL . "Done!" . PHP_EOL;
die();
