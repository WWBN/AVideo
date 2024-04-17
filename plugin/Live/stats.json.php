<?php
//exit;
header('Content-Type: application/json');
require_once '../../videos/configuration.php';

/*
  if(!requestComesFromSafePlace()) {
  _error_log("Why are you requesting this ".getSelfURI()." ".json_encode($_SERVER));
  die();
  }
 *
 */

ini_set('max_execution_time', 10);
set_time_limit(10);
//_session_write_close();
$pobj = AVideoPlugin::getDataObjectIfEnabled("Live");
if (empty($pobj->server_type->value)) {
    ini_set('max_execution_time', 180);
    set_time_limit(180);
}
if (empty($pobj)) {
    die(json_encode("Plugin disabled"));
}
$live_servers_id = Live::getLiveServersIdRequest();
$cacheName = "lsid_{$live_servers_id}_" . md5($global['systemRootPath'] . json_encode($_REQUEST));
$cacheHandler = new LiveCacheHandler();
//$json = $cacheHandler->getCache( $cacheName, 30);
//_error_log(json_encode(ObjectYPT::getLastUsedCacheInfo()));
//var_dump(ObjectYPT::getLastUsedCacheInfo(), $json);exit;

$timeName = "stats.json.php";
TimeLogStart($timeName);
if (empty($json)) {
    TimeLogEnd($timeName, __LINE__);
    $json = getStatsNotifications();
    TimeLogEnd($timeName, __LINE__);
    //var_dump(ObjectYPT::getLastUsedCacheInfo(), $json);exit;
    //$cacheHandler->setCache($json);
    TimeLogEnd($timeName, __LINE__);
}
TimeLogEnd($timeName, __LINE__);
$json = object_to_array($json);
TimeLogEnd($timeName, __LINE__);

//var_dump($json);exit;
// check if application is online
    TimeLogEnd($timeName, __LINE__);
if (!empty($_REQUEST['name'])) {
    TimeLogEnd($timeName, __LINE__);
    $json['msg'] = 'OFFLINE';
    $json['name'] = $_REQUEST['name'];
    if (!empty($json['applications'])) {
        foreach ($json['applications'] as $value) {
            if (!empty($value['key']) && $value['key'] == $_REQUEST['name']) {
                $json['msg'] = 'ONLINE';
                break;
            }
        }
    }
    TimeLogEnd($timeName, __LINE__);
    if (!empty($json['hidden_applications'])) {
        foreach ($json['hidden_applications'] as $value) {
            if (!empty($value['key']) && $value['key'] == $_REQUEST['name']) {
                $json['msg'] = 'ONLINE';
                break;
            }
        }
    }
    TimeLogEnd($timeName, __LINE__);
}
echo json_encode($json);
