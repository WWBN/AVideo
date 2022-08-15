<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams_logs.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Live');
      

if(empty($_POST['responseToken'])){
    $request = file_get_contents("php://input");
    _error_log("restreamer log add.json.php php://input {$request}");
    $robj = json_decode($request);
    foreach ($robj as $key => $value) {
        $_POST[$key] = object_to_array($value);
    }
}

$string = decryptString($_POST['responseToken']);

if(empty($string)){
   forbiddenPage('Invalid responseToken');
   _error_log("Invalid responseToken {$_POST['responseToken']}");
}

$token = json_decode($string);

if(!User::isAdmin()){
    if(empty($token->users_id)){
        forbiddenPage('Invalid token');
    }
    if($token->time < strtotime('-10 minutes')){
        forbiddenPage('Token expired');
    }
}

_error_log('add.json.php restream log POST '.json_encode($_POST));
_error_log('add.json.php restream log token '.json_encode($token));

$o = new Live_restreams_logs(@$_POST['id']);
$o->setRestreamer($_POST['restreamerURL']);
$o->setM3u8($_POST['m3u8']);
$o->setLogFile($_POST['logFile']);
$o->setJson(json_encode($_POST));
$o->setLive_transmitions_history_id($token->liveTransmitionHistory_id);
$o->setLive_restreams_id($_POST['live_restreams_id']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
