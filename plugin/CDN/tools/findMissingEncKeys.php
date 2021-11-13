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

$storages = array('https://storage.wetube.club/', 'https://storage1.wetube.club/');

ob_end_flush();
set_time_limit(300);
ini_set('max_execution_time', 300);

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;
$path = getVideosDir();
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;

$countSiteIdNotEmpty = 0;
$countStatusNotActive = 0;
$countMoved = 0;

$videosDir = getVideosDir();
$errorsFound = 0;
foreach ($videos as $value) {
    $count++;
    $videos_id = $value['id'];
    $list = CDNStorage::getLocalFolder($videos_id);
    //echo "videos_id = {$videos_id} Files found " . count($list) . PHP_EOL;
    $m3u8 = false;
    $enckey = false;
    foreach ($list as $file) {
        if (is_array($file)) {
            foreach ($file as $file2) {
                if (preg_match('/index.m3u8$/', $file2)) {
                    $m3u8 = true;
                } else if (preg_match('/enc.*.key$/', $file2)) {
                    $enckey = true;
                }
            }
        } else {
            if (preg_match('/index.m3u8$/', $file)) {
                $m3u8 = true;
            } else if (preg_match('/enc.*.key$/', $file)) {
                $enckey = true;
            }
        }
    }
    if ($m3u8 && !$enckey) {
        $errorsFound++;
        //$video = Video::getVideoLight($value);
        $paths = Video::getPaths($value['filename']);
        echo "[$errorsFound] Missing enc key for video {$videos_id} {$paths['path']}" . PHP_EOL;
        
        foreach ($storages as $s) {
            $url = "{$value}tools/getenckey.json.php?folder={$value['filename']}";
            echo "{$url}" . PHP_EOL;
            $content = file_get_contents("{$s}tools/getenckey.json.php?folder={$value['filename']}");
            if(!empty($content)){
                var_dump($content);
                break;
            }
        }
    }
}


echo PHP_EOL . " Done! " . PHP_EOL;
die();

