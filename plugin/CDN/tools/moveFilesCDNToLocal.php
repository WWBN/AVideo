<?php

//use Amp\Parallel\Worker;
use Amp\Promise;

use Amp\Deferred;
use Amp\Loop;

$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$isCDNEnabled = AVideoPlugin::isEnabledByName('CDN');

if (empty($isCDNEnabled)) {
    return die('Plugin disabled');
}

ob_end_flush();
set_time_limit(0);
ini_set('max_execution_time', 0);
/**
 * @var mixed[] $global
 */
$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;
$path = getVideosDir();
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;

$countStatusNotActive = 0;
$countMoved = 0;

$videos_id_to_move = [];

foreach ($videos as $key => $value) {
    $count++;
    //echo "{$count}/{$total} Checking {$global['webSiteRootURL']}v/{$value['id']} {$value['title']}" . PHP_EOL;
    if (empty($value['sites_id'])) {
        echo "sites_id is empty {$value['sites_id']}" . PHP_EOL;
        continue;
    }
    $videos_id_to_move[] = $value['id'];
    echo "{$key}/{$total} added to move {$global['webSiteRootURL']}v/{$value['id']} {$value['title']}" . PHP_EOL;
}

function download($videos_id) {
    $deferred = new Deferred();
    $response = $response = CDNStorage::ftp_get($videos_id);
    $deferred->resolve($response);
    return $deferred->promise();
}

function runLoop() {
    global $videos_id_to_move;
    $videos_id = array_shift($videos_id_to_move);
    if (empty($videos_id)) {
        return false;
    }
    download($videos_id)->onResolve(function (Throwable $error = null, $response = null) {
        if ($error) {
            _error_log("download: asyncOperation1 fail -> " . $error->getMessage());
        } else {
            _error_log("download: asyncOperation1 result -> " . json_encode($response));
        }
        runLoop();
    });
}

Loop::run(function () {
     _error_log("download: runLoop 1 ");
    runLoop();
     _error_log("download: runLoop 2 ");
    runLoop();
});


echo "StatusNotActive=$countStatusNotActive; Moved=$countMoved;" . PHP_EOL;
echo PHP_EOL . " Done! " . PHP_EOL;
die();
