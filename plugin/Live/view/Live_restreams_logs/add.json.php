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

$o = new Live_restreams_logs(@$_POST['id']);
$o->setRestreamer($_POST['restreamerURL']);
$o->setM3u8($_POST['m3u8']);
$o->setDestinations(json_encode($_POST['restreamsDestinations'])0;
$o->setLogFile($_POST['logFile']);
$o->setUsers_id($token->users_id);
$o->setJson(json_encode($_POST));

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
