<?php
header('Content-Type: application/json');
require_once '../../videos/configuration.php';
session_write_close();

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->online = false;

if(empty($_GET['name'])){
    $obj->msg = __("Key is empty");
    die(json_encode($obj));
}

$p = AVideoPlugin::loadPluginIfEnabled("Live");

if(empty($p)){
    $obj->msg = __("Live plugin is not enabled");
    die(json_encode($obj));
}
$xml = $p->getStatsObject();
$xml = json_encode($xml);
$xml = _json_decode($xml);

$stream = false;
$lifeStream = array();
//$obj->server = $xml->server;
if(!empty($xml->server->application) && !is_array($xml->server->application)){
    $application = $xml->server->application;
    $xml->server->application = array();
    $xml->server->application[] = $application;
}
if(!empty($xml->server->application[0]->live->stream)){
    $lifeStream = $xml->server->application[0]->live->stream;
    if(!is_array($xml->server->application[0]->live->stream)){
        $lifeStream = array();
        $lifeStream[0] = $xml->server->application[0]->live->stream;
    }
}

foreach ($lifeStream as $value){
    if(!empty($value->name)){
        if($_GET['name']==$value->name){
            $obj->online = true;
            break;
        }
    }
}

$obj->error = false;

echo json_encode($obj);