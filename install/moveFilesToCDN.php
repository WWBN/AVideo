<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$isCDNEnabled = AVideoPlugin::isEnabledByName('CDN');

if(empty($isCDNEnabled)){
    return die('Plugin disabled');
}

set_time_limit(300);
ini_set('max_execution_time', 300); 

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;
$path = getVideosDir();
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;


foreach ($videos as $value) {
    $count++;
    echo "{$count}/{$total} Moving {$global['webSiteRootURL']}v/{$value['id']} {$value['title']}".PHP_EOL;
    if(!empty($value['sites_id'])){
        echo "sites_id is not empty {$value['sites_id']}".PHP_EOL;
        continue;
    }
    if($value['status'] !== Video::$statusActive){
        echo "The video status is not active {$value['status']}".PHP_EOL;
        continue;
    }
    
    //CDNStorage::moveLocalToRemote($value['id']);
    echo "{$count}/{$total} Moved done {$global['webSiteRootURL']}v/{$value['id']} {$value['title']}".PHP_EOL;
    ob_flush();
}

echo PHP_EOL." Done! ".PHP_EOL;
die();

