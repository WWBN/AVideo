<?php

header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (!file_exists($configFile)) {
        $configFile = '../videos/configuration.php';
    }
    require_once $configFile;
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->response = "";
$obj->commandURL = "";
$obj->command = $_REQUEST['command'];
$obj->live_transmition_id = $_REQUEST['live_transmition_id'];
$obj->live_servers_id = $_REQUEST['live_servers_id'];
$obj->key = "";
$obj->newkey = "";

if (empty($obj->command)) {
    $obj->msg = __("empty command");
    die(json_encode($obj));
}

if (!User::canStream()) {
    $obj->msg = __("Permition denied");
    die(json_encode($obj));
}

if (!empty($obj->live_transmition_id)) {
    $obj->live_transmition_id = intval($obj->live_transmition_id);
}

if (empty($obj->live_transmition_id)) {
    $obj->msg = __("empty live_transmition_id");
    die(json_encode($obj));
}

if (!empty($obj->live_servers_id)) {
    $obj->live_servers_id = intval($obj->live_servers_id);
}

$live = AVideoPlugin::loadPluginIfEnabled("Live");
require_once './Objects/LiveTransmition.php';

if (empty($live)) {
    $obj->msg = __("Plugin disabled");
    die(json_encode($obj));
}

$l = new LiveTransmition($obj->live_transmition_id);
$users_id = $l->getUsers_id();

if (empty($users_id)) {
    $obj->msg = __("Transmission not found");
    die(json_encode($obj));
}

if (!User::isAdmin()) {
    if ($users_id != User::getId()) {
        $obj->msg = __("You cannot drop this live");
        die(json_encode($obj));
    }
}


$obj->key = $l->getKey();
$obj->newkey = $obj->key;
if (empty($obj->key)) {
    $obj->msg = __("key cannot be empty");
    die(json_encode($obj));
}


switch ($obj->command) {
    case "record_start":
        //http://server.com/control/record/start|stop?srv=SRV&app=APP&name=NAME&rec=REC
        $obj->commandURL = Live::getStartRecordURL($obj->key, $obj->live_servers_id);
        break;
    case "record_stop":
        //http://server.com/control/record/start|stop?srv=SRV&app=APP&name=NAME&rec=REC
        $obj->commandURL = Live::getStopRecordURL($obj->key, $obj->live_servers_id);
        break;
    case "drop_publisher_reset_key":
        $obj->newkey = LiveTransmition::resetTransmitionKey($l->getUsers_id());
    case "drop_publisher":
        //http://server.com/control/drop/publisher|subscriber|client?srv=SRV&app=APP&name=NAME&addr=ADDR&clientid=CLIENTID
        $obj->commandURL = Live::getDropURL($obj->key);
        break;
    case "is_recording":
        //http://server.com/control/drop/publisher|subscriber|client?srv=SRV&app=APP&name=NAME&addr=ADDR&clientid=CLIENTID
        $obj->commandURL = Live::getIsRecording($obj->key);
        break;
    default:
        $obj->msg = "Command is invalid ($obj->command)";
        die(json_encode($obj));
        break;
}

$obj->commandURL = Live::getDropURL($l->getKey(), $obj->live_servers_id);
$obj->response = _json_decode(url_get_contents($obj->commandURL));

if (!empty($obj->response)) {
    if ($obj->response->error) {
        $obj->msg = $obj->response->msg;
    } else {
        $obj->error = false;
    }
}

die(json_encode($obj));
