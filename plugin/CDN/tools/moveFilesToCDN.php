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

$onlyExtension = trim(@$argv[1]);
$index = intval(@$argv[2]);

ob_end_flush();
set_time_limit(300);
ini_set('max_execution_time', 300);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;
$path = getVideosDir();
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;

$countSiteIdNotEmpty = 0;
$countStatusNotActive = 0;
$countMoved = 0;

$sites_id_to_move = [];

foreach ($videos as $value) {
    $count++;
    //echo "{$count}/{$total} Checking {$global['webSiteRootURL']}v/{$value['id']} {$value['title']}" . PHP_EOL;
    if (!empty($value['sites_id'])) {
        echo "sites_id is not empty {$value['sites_id']}" . PHP_EOL;
        //CDNStorage::createDummyFiles($value['id']);
        $countSiteIdNotEmpty++;
        // make sure it is dummy files
        //CDNStorage::createDummyFiles($value['id']);
        continue;
    }
    if ($value['status'] !== Video::$statusActive) {
        $countStatusNotActive++;
        //echo "The video status is not active {$value['status']}" . PHP_EOL;
        continue;
    }
    $countMoved++;
    $sites_id_to_move[] = $value['id'];
    echo "{$key}/{$total} added to move {$global['webSiteRootURL']}v/{$value['id']} {$value['title']}" . PHP_EOL;
}

$total = count($sites_id_to_move);
foreach ($sites_id_to_move as $key => $value) {
    if (!empty($index) && $key<$index) {
        continue;
    }
    echo "{$key}/{$total} Start move {$value} onlyExtension={$onlyExtension}" . PHP_EOL;
    $startF = microtime(true);
    $response = CDNStorage::put($value, 4, $onlyExtension);
    if (empty($response)) {
        echo "{$key}/{$total} ERROR " . PHP_EOL;
    } else {
        $endF = microtime(true) - $startF;
        $ETA = ($total - $key + 1) * $endF;
        $ps = humanFileSize($response['totalBytesTransferred'] / ($endF));
        echo "{$key}/{$total} Moved done {$value} filesCopied={$response['filesCopied']} totalBytesTransferred=" . humanFileSize($response['totalBytesTransferred']) . " in " . secondsToDuration($endF) . " ETA: " . secondsToDuration($ETA) . " " . $ps . 'ps' . PHP_EOL;
    }
}

echo "SiteIdNotEmpty = $countSiteIdNotEmpty; StatusNotActive=$countStatusNotActive; Moved=$countMoved;" . PHP_EOL;
echo PHP_EOL . " Done! " . PHP_EOL;
die();
