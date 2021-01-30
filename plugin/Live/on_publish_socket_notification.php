<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
header('Content-Type: application/json');

if (!isCommandLineInterface()) {
    die('Command line only');
}

if (count($argv) < 3) {
    die('Please pass all argumments');
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$users_id = intval($argv[1]);
$m3u8 = $argv[2];

if (AVideoPlugin::isEnabledByName('YPTSocket')) {
    _error_log("NGINX Live::on_publish_socket_notification");
    $is200 = false;
    for ($itt = 5; $itt > 0; $itt--) {
        if (!$is200 = isURL200($m3u8)) {
            //live is not ready request again
            sleep($itt);
        } else {
            break;
        }
    }
    if ($is200) {
        $array['stats'] = LiveTransmitionHistory::getStatsAndAddApplication($obj->liveTransmitionHistory_id);
    } else {
        $array['stats'] = getStatsNotifications();
    }

    _error_log("NGINX Live::on_publish_socket_notification sendSocketMessageToAll");
    $socketObj = sendSocketMessageToAll($array, "socketLiveONCallback");
    _error_log("NGINX Live::on_publish_socket_notification  endSocketMessageToAll END");
}

die(json_encode($obj));
