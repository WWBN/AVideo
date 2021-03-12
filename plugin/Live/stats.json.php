<?php
header('Content-Type: application/json');
require_once '../../videos/configuration.php';

if(!requestComesFromSafePlace()){
    _error_log("Why are you requesting this ".getSelfURI()." ".json_encode($_SERVER));
    die();
}

ini_set('max_execution_time', 10);
set_time_limit(10);
session_write_close();
$pobj = AVideoPlugin::getDataObjectIfEnabled("Live");

if (empty($pobj)) {
    die(json_encode("Plugin disabled"));
}
$live_servers_id = Live::getLiveServersIdRequest();
$cacheName = DIRECTORY_SEPARATOR."getStats".DIRECTORY_SEPARATOR."live_servers_id_{$live_servers_id}".DIRECTORY_SEPARATOR."_statsCache_".md5($global['systemRootPath']. json_encode($_REQUEST));
        
$json = ObjectYPT::getCache($cacheName, $pobj->cacheStatsTimout, false);
if(empty($json)){
    $json = getStatsNotifications();
    ObjectYPT::setCache($cacheName, $json);
}
echo json_encode($json);