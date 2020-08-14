<?php

header('Content-Type: application/json');
require_once '../../videos/configuration.php';
ini_set('max_execution_time', 10);
set_time_limit(10);
session_write_close();
$pobj = AVideoPlugin::getDataObjectIfEnabled("Live");

if (empty($pobj)) {
    die(json_encode("Plugin disabled"));
}
$live_servers_id = Live::getCurrentLiveServersId();
$cacheName = "statsCache_{$live_servers_id}_".md5($global['systemRootPath']. json_encode($_REQUEST));
$json = ObjectYPT::getCache($cacheName, $pobj->cacheStatsTimout);
if(empty($json)){
    $json = Live::getStats();
    if(!is_array($json) && is_object($json)){
        $json = object_to_array($json);
    }
    $appArray = AVideoPlugin::getLiveApplicationArray();
    if(!empty($appArray)){
        if(empty($json)){
            $json = new stdClass();
        }
        $json['error'] = false;
        if(empty($json['msg'])){
            $json['msg'] = "OFFLINE";
        }
        $json['nclients'] = count($appArray);
        if(empty($json['applications'])){
            $json['applications'] = array();
        }
        $json['applications'] = array_merge($json['applications'] , $appArray);
    }
    
    ObjectYPT::setCache($cacheName, $json);
}
echo json_encode($json);