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
    echo "{$count}/{$total} Moving video {$value['title']}".PHP_EOL;
    if(!empty($value['sites_id'])){
        echo "sites_id is not empty {$value['sites_id']}".PHP_EOL;
    }
    
    CDNStorage::moveLocalToRemote($value['id']);
    ob_flush();
}
echo PHP_EOL." Deleting cache ... ";
ObjectYPT::deleteALLCache();
$videosDir = Video::getStoragePath(); 
exec("chown -R www-data:www-data {$videosDir}");
exec("chmod -R 755 {$videosDir}");
echo PHP_EOL." Done! ".PHP_EOL;
die();

