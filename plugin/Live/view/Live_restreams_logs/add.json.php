<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams_logs.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Live');
      
$token = decryptString($_POST['responseToken']);

if(!User::isAdmin()){
    if(empty($token->users_id)){
        forbiddenPage('Invalid token');
    }
    if($token->time < strtotime('-10 minutes')){
        forbiddenPage('Token expired');
    }
}

$o = new Live_restreams_logs(@$_POST['id']);
$o->setRestreamer($_POST['restreamer']);
$o->setM3u8($_POST['m3u8']);
$o->setDestinations($_POST['destinations']);
$o->setLogFile($_POST['logFile']);
$o->setUsers_id($_POST['users_id']);
$o->setJson($_POST['json']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
