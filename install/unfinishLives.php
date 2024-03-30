<?php
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
AVideoPlugin::loadPlugin('Live');
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

$stats = Live::getStatsApplications(true);

foreach ($stats as $key => $live) {
    if (!empty($live['key'])) {
        echo "key= {$live['key']}".PHP_EOL;
        $row = LiveTransmitionHistory::getLatest($live['key'], $live['live_servers_id']);
        
        echo "id={$row['id']} finished= {$row['finished']}".PHP_EOL;
        if (!empty($row['finished'])) {
            LiveTransmitionHistory::unfinishFromTransmitionHistoryId($row['id']);
            var_dump($resp, $unfinishFromTransmitionHistoryIdSQL);
            echo "id={$row['id']} unfinished".PHP_EOL;
        }else{
            $row = LiveTransmition::keyExists($live['key']);
            if(!empty($row)){
                $lth = new LiveTransmitionHistory();
                $lth->setTitle($row['title']);
                $lth->setDescription($row['description']);
                $lth->setKey($live['key']);
                $lth->setUsers_id($row['users_id']);
                $lth->setLive_servers_id($live['live_servers_id']);
                $id = $lth->save();
                echo ("unfinishAllFromStats saving LiveTransmitionHistory [{$id}]").PHP_EOL;
                echo "not empty id={$row['id']}".PHP_EOL;
            }else{

            }
        }
    }
}


Live::finishAllFromStats();
Live::unfinishAllFromStats(true);
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);


$stats = Live::getStats(1);
foreach ($stats as $key => $server) {
    if (is_array($server) || is_object($server)) {
        foreach ($server as $key2 => $live) {
            if (!empty($live->key)) {
                echo __LINE__ . " {$live->key} ".PHP_EOL;
            } else if (!empty($live['key'])) {
                echo __LINE__ . " {$live['key']} ".PHP_EOL;
            } else {
                if ($key2 == 'applications' && is_array($live)) {
                    foreach ($live as $key3 => $value3) {
                        var_dump($value3);
                    }
                }
            }
        }
    }
}

$stats = Live::getStatsApplications(1);
foreach ($stats as $key => $live) {
    if (!empty($live['key'])) {
        echo __LINE__ . " {$live['key']} ".PHP_EOL;
        if (!empty($row['finished'])) {
            echo __LINE__ . " {$live['key']} ".PHP_EOL;
        }else{
            $row = LiveTransmition::keyExists($live['key']);
            if(!empty($row)){
                echo __LINE__ . " {$live['key']} ".PHP_EOL;
                $lth = new LiveTransmitionHistory();
                $lth->setTitle($row['title']);
                $lth->setDescription($row['description']);
                $lth->setKey($live['key']);
                $lth->setUsers_id($row['users_id']);
                $lth->setLive_servers_id($live['live_servers_id']);
                $id = $lth->save();
                echo ("unfinishAllFromStats saving LiveTransmitionHistory {$live['key']} [{$id}]".PHP_EOL);
            }else{                
                echo __LINE__ . " {$live['key']} ".PHP_EOL;
            }
        }
    }else{
        var_dump($live);
    }
}