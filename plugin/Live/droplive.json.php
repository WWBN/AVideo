<?php

header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->response = "";
if(!User::canStream()){
    $obj->msg = __("Permition denied");
    die(json_encode($obj));
}

$live_transmition_id = 0;
if(!empty($_REQUEST['live_transmition_id'])){
    $live_transmition_id = intval($_REQUEST['live_transmition_id']);
}

if(empty($live_transmition_id)){
    $obj->msg = __("empty live_transmition_id");
    die(json_encode($obj));
}

$live_servers_id = 0;
if(!empty($_REQUEST['live_servers_id'])){
    $live_servers_id = intval($_REQUEST['live_servers_id']);
}

$live = AVideoPlugin::loadPluginIfEnabled("Live");
require_once './Objects/LiveTransmition.php';

if(empty($live)){
    $obj->msg = __("Plugin disabled");
    die(json_encode($obj));
}

$l = new LiveTransmition($live_transmition_id);
$users_id = $l->getUsers_id();

if(empty($users_id)){
    $obj->msg = __("Transmission not found");
    die(json_encode($obj));
}

if(!User::isAdmin()){
    if($users_id!= User::getId()){
        $obj->msg = __("You cannot drop this live");
        die(json_encode($obj));
    }
}

$dropURL = Live::getDropURL($l->getKey(), $live_servers_id);

$obj->error = false;
$obj->response = url_get_contents($dropURL);;

die(json_encode($obj));
