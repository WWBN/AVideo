<?php

//streamer config
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface() && !User::isAdmin()) {
    return die('Command Line only');
}

if (!$obj = AVideoPlugin::getDataObjectIfEnabled('Scheduler')) {
    return die('Scheduler is disabled');
}

// check socket
if ($obj->watchDogSocket) {
    if ($objParam = AVideoPlugin::getDataObjectIfEnabled('YPTSocket')) {
        if (!is_port_open($objParam->port)) {
            _error_log("WatchDog: socket port is not opened [{$objParam->port}]");
            $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';
            restartServer();
        }else{
            //_error_log("WatchDog: socket port is opened [{$objParam->port}]");
        }
    }
}

if ($objParam = AVideoPlugin::getDataObjectIfEnabled('Live')) {
    $nginxFile = '/usr/local/nginx/sbin/nginx';
    if (file_exists($nginxFile)) {
        // check live
        $port = Live::getPlayerDestinationPort();
        $address = Live::getPlayerDestinationHost();

        if ($obj->watchDogLiveServer) {
            if (!is_port_open($port)) {
                _error_log("WatchDog: Live port is not opened [{$port}]");
                exec("{$nginxFile} -s stop");
                execAsync("{$nginxFile}");
            }else{
                //_error_log("WatchDog: Live port is opened [{$port}]");
            }
        }

        if ($obj->watchDogLiveServerSSL) {
            // check live ssl
            if (!is_ssl_certificate_valid($port, $address)) {
                _error_log("WatchDog: Live SSL is invalid [port=$port, address=$address]");
                exec("{$nginxFile} -s stop");
                execAsync("{$nginxFile}");
            }else{
                //_error_log("WatchDog: Live SSL is valid [port=$port, address=$address]");
            }
        }
    }else{
        //_error_log("WatchDog: nginx file not found {$nginxFile}");
    }
}
