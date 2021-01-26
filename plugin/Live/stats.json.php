<?php
global $_statsAlreadyConsumed;
if(!empty($_statsAlreadyConsumed)){
    die(json_encode(array()));
}
$_statsAlreadyConsumed = 1;
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
$live_servers_id = Live::getCurrentLiveServersId();
$cacheName = "statsCache_{$live_servers_id}_".md5($global['systemRootPath']. json_encode($_REQUEST));
$json = ObjectYPT::getSessionCache($cacheName, $pobj->cacheStatsTimout);
if(empty($json)){
    $json = getStatsNotifications();
    ObjectYPT::setSessionCache($cacheName, $json);
}
echo json_encode($json);