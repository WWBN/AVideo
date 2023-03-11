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

$S3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');

if (empty($S3)) {
    return die('Plugin S3 disabled');
}
ob_end_flush();
set_time_limit(1200);
ini_set('max_execution_time', 1200);

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;
//$videos = Video::getAllVideosLight("", false, true, false);

$path = getVideosDir();

$startInIndex = intval(@$argv[1]);
if($startInIndex < 0){
    $sort = 'DESC';
}

$sql = "SELECT * FROM  videos WHERE 1=1 ORDER BY id $sort ";
$res = sqlDAL::readSql($sql);
$videos = sqlDAL::fetchAllAssoc($res);
sqlDAL::close($res);

$count = 0;
$total = count($videos);

echo "{$count}/{$total} Start" . PHP_EOL;
$client = CDNStorage::getStorageClient();

foreach ($videos as $key => $value) {
    $count++;
    if($count<$startInIndex){
        echo "{$count}/{$total} Skip [{$value['id']}] " . PHP_EOL;
        continue;
    }
    $start = microtime(true);
    echo PHP_EOL.PHP_EOL."{$count}/{$total} checking [{$value['id']}] {$value['title']}" . PHP_EOL;
    $destination = Video::getPathToFile($value['filename'], true);

    foreach (glob($destination . '*.{mp3,mp4,webm}', GLOB_BRACE) as $file) {
        $filesize = filesize($file);
        $filesizeHuman = humanFileSize($filesize);        
        if ($filesize && isDummyFile($file)) {
            $filename = basename($file);
            $url = $S3->getURL($filename);
            
            $remote_size = getUsageFromURL($url);
            $relative = str_replace($path, '', $file);
            $filesizeCDN = $client->size($relative);
            if($filesizeCDN >= $remote_size){
                echo "{$count}/{$total} Downloading canceled, size is the same or bigger ".humanFileSize($filesizeCDN)." >= ".humanFileSize($remote_size)." {$filename}". PHP_EOL;
                continue;
            }else{
                echo "{$count}/{$total} Downloading ".humanFileSize($filesizeCDN)." >= ".humanFileSize($remote_size) ." [{$value['id']}] {$value['title']} {$filename}" . PHP_EOL;
            }
            
            if ($result = copy_remotefile_if_local_is_smaller($url, $file)) {
                echo "{$count}/{$total} SUCCESS 1 [{$value['id']}] {$value['title']} ". humanFileSize($result) . PHP_EOL;
            } else { 
                echo "{$count}/{$total} FAIL 1, try again [{$value['id']}] {$value['title']} " . PHP_EOL;
                if ($S3->copy_from_s3($filename, $file)) {
                    $filesize = filesize($file);
                    $filesizeHuman = humanFileSize($filesize);
                    echo "{$count}/{$total} SUCCESS 2 [{$value['id']}] {$value['title']} [$filesizeHuman]" . PHP_EOL;
                } else {
                    echo "{$count}/{$total} FAIL 2 [{$value['id']}] {$value['title']}" . PHP_EOL;
                }
            }
        } else {
            echo "{$count}/{$total} Not Dummy [{$value['id']}] {$value['title']} $file " . PHP_EOL;
        }
    }
    if (CDNStorage::isMoving($value['id'])) {
        echo "videos_id = {$value['id']} {$value['title']} Is moving ". PHP_EOL;
    } else {
        echo "videos_id = {$value['id']} {$value['title']} moving {$value['sites_id']} " . PHP_EOL;
        CDNStorage::put($value['id'], 4);
        CDNStorage::createDummyFiles($value['id']);
    }
    $end = microtime(true)-$start;
    echo "*** id={$value['id']} Downloading done ". seconds2human($end)." ETA=".seconds2human($end*($total-$count))." ***" . PHP_EOL;
    
}

echo "{$count}/{$total} END" . PHP_EOL;
echo PHP_EOL . " Done! " . PHP_EOL;
die();
