<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
@header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (empty($liveTransmitionHistory_id) || empty($users_id) || empty($m3u8)) {
    _error_log("NGINX Live::on_publish_socket_notification start");
    if (!isCommandLineInterface()) {
        $obj->msg = "NGINX Live::on_publish_socket_notification Command line only";
        _error_log($obj->msg);
        die(json_encode($obj));
    }

    if (count($argv) < 4) {
        $obj->msg = "NGINX Live::on_publish_socket_notification Please pass all argumments";
        _error_log($obj->msg);
        die(json_encode($obj));
    }

    $users_id = intval($argv[1]);
    $m3u8 = $argv[2];
    $liveTransmitionHistory_id = $argv[3];
}

if (AVideoPlugin::isEnabledByName('YPTSocket')) {
    $lth = new LiveTransmitionHistory($liveTransmitionHistory_id);
    if (empty($array)) {
        $array = setLiveKey($lth->getKey(), $lth->getLive_servers_id());
        $parameters = Live::getLiveParametersFromKey($array['key']);
        $array['cleanKey'] = $parameters['cleanKey'];
        $array['stats'] = LiveTransmitionHistory::getStatsAndRemoveApplication($row['id']);
    }
    if (empty($array['key'])) {
        $array['key'] = $lth->getKey();
        $array['live_servers_id'] = $lth->getLive_servers_id();
    }
    _error_log("NGINX Live::on_publish_socket_notification ($m3u8)");
    $isLive = false;
    $_REQUEST['live_servers_id'] = $lth->getLive_servers_id();
    $max_execution_time = 60;
    ini_set('max_execution_time', $max_execution_time);
    for ($itt = ($max_execution_time/5)-1; $itt > 0; $itt--) {
        //if (!$isLive = isURL200($m3u8, true)) {
        if (!$isLive = Live::isLiveAndIsReadyFromKey($lth->getKey(), $lth->getLive_servers_id(), $lth->getLive_index(), true)) {
            _error_log("live is not ready [$itt] request again in 5 seconds ($m3u8)");
            //live is not ready request again
            sleep(5);
        } else {
            _error_log("live is ready [$itt] ($m3u8)");
            break;
        }
    }
    $array['live_transmitions_history_id'] = $liveTransmitionHistory_id;
    $array['isPrivate'] = LiveTransmitionHistory::isPrivate($liveTransmitionHistory_id);
    $array['isPasswordProtected'] = LiveTransmitionHistory::isPasswordProtected($liveTransmitionHistory_id);
    $array['isRebroadcast'] = LiveTransmitionHistory::isRebroadcast($liveTransmitionHistory_id);
    $array['users_id'] = $users_id;
    if(!empty($array['key'])){
        $array['title'] = Live::getTitleFromKey($array['key'], $array['title']);
    }else{
        $array['title'] = '';
    }

    if ($isLive) {
        _error_log("NGINX Live::on_publish_socket_notification is200");
        $array['stats'] = LiveTransmitionHistory::getStatsAndAddApplication($liveTransmitionHistory_id);
    } else {
        _error_log("NGINX Live::on_publish_socket_notification isNOT200");
        $array['stats'] = getStatsNotifications();
    }
    $obj->error = false;

    $array['cleanKey'] = Live::cleanUpKey($array['key']);
    $socketObj = Live::notifySocketStats("socketLiveONCallback", $array);
}

_error_log("NGINX Live::on_publish_socket_notification end");
die(json_encode($obj));
