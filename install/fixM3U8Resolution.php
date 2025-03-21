<?php
//streamer config
require_once __DIR__ . '/../videos/configuration.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    return die('Command Line only');
}
$global['skipModifyURL'] = 1;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$global['rowCount'] = 99999;
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;

$cdnObj = AVideoPlugin::getDataObjectIfEnabled('CDN');


$resolutions = [
    240   => 426,
    360   => 640,
    480   => 854,
    540   => 960,
    720   => 1280,
    1080  => 1920,
    1440  => 2560,
    2160  => 3840
];

foreach ($videos as $video) {
    $count++;
    //echo "FIX: {$count}/{$total} ({$video['type']}) [{$video['id']}] {$video['title']}".PHP_EOL;
    if ($video['type'] !== 'video') {
        continue;
    }
    $sources = getVideosURLOnly($video['filename'], false);
    if (empty($sources['m3u8'])) {
        continue;
    }

    $content = file_get_contents($sources['m3u8']['url']);
    if (preg_match_all('/RESOLUTION=-2x([0-9]+)/i', $content, $matches)) {
        //var_dump($content);
        foreach ($matches[1] as $key => $value) {
            $height = intval($value);
            if (!empty($resolutions[$height])) {
                $content = str_replace($matches[0][$key], "RESOLUTION={$resolutions[$height]}x{$height}", $content);
            }
        }
        file_put_contents($sources['m3u8']['path'], $content);
        //var_dump($sources['m3u8'], $content);
        echo "FIX : {$count}/{$total} ({$sources['m3u8']['url']}) [{$video['id']}] {$video['title']}" . PHP_EOL;

        if (!empty($cdnObj) && !empty($cdnObj->enable_storagej)) {
            try {
                CDNStorage::putUsingAPI([$resp['path']]);
            } catch (\Throwable $th) {
                _error_log("HLSAudioManager CDNStorage::put API error use FTP " . $th->getMessage());
                CDNStorage::putUsingFTP([$resp['path']], 1);
            }
            CDNStorage::createDummyFiles($video['id']);
        }
    }else{
        echo "SKIP: {$count}/{$total} ({$sources['m3u8']['url']}) [{$video['id']}] {$video['title']}" . PHP_EOL;
    }
}

if (!empty($cdnObj) && !empty($cdnObj->enable_storagej)) {
    CDN::purgeCache();
}

die();
