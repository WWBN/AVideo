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
$cacheName = "statsCache_{$live_servers_id}_".md5($global['systemRootPath']);
$json = ObjectYPT::getCache($cacheName, $pobj->cacheStatsTimout);
if(empty($json)){
    $json = Live::getStats();
    ObjectYPT::setCache($cacheName, $json);
}
echo json_encode($json);