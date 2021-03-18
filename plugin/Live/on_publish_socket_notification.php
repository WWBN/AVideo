<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
header('Content-Type: application/json');

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
    _error_log("NGINX Live::on_publish_socket_notification ($m3u8)");
    $is200 = false;
    for ($itt = 5; $itt > 0; $itt--) {
        if (!$is200 = isURL200($m3u8)) {
            _error_log("live is not ready request again in {$itt} seconds");
            //live is not ready request again
            sleep($itt);
        } else {
            break;
        }
    }
    $array['live_transmitions_history_id'] = $liveTransmitionHistory_id;
    $array['users_id'] = $users_id;
    
    if ($is200) {
        $array['stats'] = LiveTransmitionHistory::getStatsAndAddApplication($liveTransmitionHistory_id);
    } else {
        $array['stats'] = getStatsNotifications();
    }
    $obj->error = false;
    $socketObj = Live::notifySocketStats("socketLiveONCallback", $array);
}

_error_log("NGINX Live::on_publish_socket_notification end");
die(json_encode($obj));
