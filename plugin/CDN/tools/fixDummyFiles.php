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

$index = intval(@$argv[1]);

ob_end_flush();
set_time_limit(300);
ini_set('max_execution_time', 300);

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;
$path = getVideosDir();
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);

$sql = "SELECT * FROM  videos WHERE 1=1 ORDER BY id DESC LIMIT 300 ";
$res = sqlDAL::readSql($sql);
$videos = sqlDAL::fetchAllAssoc($res);
$total = count($videos);
sqlDAL::close($res);
foreach ($videos as $key => $value) {
    $count++;
    if (empty($value['sites_id'])) {
        //echo "The video status is not active {$value['status']}" . PHP_EOL;
        continue;
    }
    $filesAffected = CDNStorage::createDummyFiles($value['id']);
    if (empty($filesAffected)) {
        echo "{$key}/{$total} ERROR " . PHP_EOL;
    } else {
        echo "{$key}/{$total} filesAffected={$filesAffected} " . PHP_EOL;
    }
}

echo PHP_EOL . " Done! " . PHP_EOL;
die();
