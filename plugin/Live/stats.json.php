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

$timeName = "stats.json.php";
TimeLogStart($timeName);
$json = getStatsNotifications();
//var_dump($json);exit;
TimeLogEnd($timeName, __LINE__);
$json = object_to_array($json);
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
//var_dump($json);exit;
echo json_encode($json);
