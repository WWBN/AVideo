<?php
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
AVideoPlugin::loadPlugin('Live');
/*
$rows = Live_servers::getAllActive();

foreach ($rows as $liveS) {
    echo "id= {$liveS['id']}".PHP_EOL;
    $lives = LiveTransmitionHistory::getActiveLives($liveS['id'], true);
    foreach ($lives as $live) {
        echo "key ={$live['key']}".PHP_EOL;
        $found = false;
        foreach ($stats as $liveFromStats) {
            echo "compare ={$liveFromStats['key']} == {$live['key']}".PHP_EOL;
            if (!empty($liveFromStats['key']) && $liveFromStats['key'] == $live['key'] ) {
                $found = true;
                break;
            }
        }
        if(!$found){
            LiveTransmitionHistory::finishFromTransmitionHistoryId($live['id']);
        }
    }
}
*/

$stats = Live::getStatsApplications($force_recreate);

foreach ($stats as $key => $live) {
    if (!empty($live['key'])) {
        echo "key= {$live['key']}".PHP_EOL;
        $row = LiveTransmitionHistory::getLatest($live['key'], $live['live_servers_id']);
        echo "id={$row['id']} finished= {$row['finished']}".PHP_EOL;
        if (!empty($row['finished'])) {
            $resp = LiveTransmitionHistory::unfinishFromTransmitionHistoryId($row['id']);
            var_dump($resp);
            echo "id={$row['id']} unfinished".PHP_EOL;
        }else{
            echo "not empty id={$row['id']}".PHP_EOL;
        }
    }
}