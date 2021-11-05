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
foreach ($videos as $value) {
    $count++;
    //echo "{$count}/{$total} Checking {$global['webSiteRootURL']}v/{$value['id']} {$value['title']}" . PHP_EOL;
    if (empty($value['sites_id'])) {
        echo "sites_id is empty {$value['id']} {$value['title']}" . PHP_EOL;
        ob_flush();
        continue;
    }
    if ($value['status'] !== Video::$statusActive) {
        $countStatusNotActive++;
        echo "The video status is not active {$value['status']}" . PHP_EOL;
        ob_flush();
        continue;
    }
    echo "*** Checking {$value['id']} {$value['title']}" . PHP_EOL;
    $videos_id = $value['id'];
    $list = self::getFilesListLocal($videos_id);    
    foreach ($list as $value) {
        if(preg_match('/index.m3u8$/', $value['local_path'])){
            ob_flush();
            echo "Check {$value['local_path']}" . PHP_EOL;
        }
    }
    
}
echo PHP_EOL . " Done! " . PHP_EOL;
die();

