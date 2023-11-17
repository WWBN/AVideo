<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
AVideoPlugin::loadPlugin('Live');
$stats = Live::getStats($force_recreate);

foreach ($stats as $server) {
    if (is_array($server) || is_object($server)) {
        foreach ($server as $live) {
            if (!empty($live->key)) {
                var_dump($live->key);
                $row = LiveTransmitionHistory::getLatest($live->key, @$live->live_servers_id);
                if (!empty($row['finished'])) {
                    LiveTransmitionHistory::unfinishFromTransmitionHistoryId($row['id']);
                }
            }else{
                echo 'Key is empty error ';
            }
        }
    }else{
        echo 'Server error ';
        var_dump($server);
    }
}