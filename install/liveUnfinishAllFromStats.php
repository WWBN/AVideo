<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
AVideoPlugin::loadPlugin('Live');
$p = AVideoPlugin::loadPlugin("Live");
$xml = $p->getStatsObject($live_servers_id, true);
var_dump(url_get_contents('https://veestreamz.com:8443/stat', 4));
$stats = Live::getStats(true);
var_dump($stats);
foreach ($stats as $server) {
    if (is_array($server) || is_object($server)) {
        foreach ($server as $live) {
            if (!empty($live->key)) {
                var_dump($live->key);
                $row = LiveTransmitionHistory::getLatest($live->key, @$live->live_servers_id);
                if (!empty($row['finished'])) {
                    LiveTransmitionHistory::unfinishFromTransmitionHistoryId($row['id']);
                }else{
                    echo "Key not found key={$live->key}, live_servers_id={$live->live_servers_id}".PHP_EOL;
                }
            }else{
                echo 'Key is empty error '.PHP_EOL;
            }
        }
    }else{
        echo 'Server error ';
        var_dump($server);
    }
}