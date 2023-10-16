<?php
require_once '../../videos/configuration.php';
_session_write_close();
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if(empty($_REQUEST['platform'])){
    $obj->msg = "Platform is empty";   
    die(json_encode($obj));
}

$platform = array();

foreach (CustomizeUser::getSocialMedia() as $key => $value) {
    if($_REQUEST['platform'] === $key){
        $platform = $value;
    }
}

if(empty($platform)){
    $obj->msg = "Platform {$_REQUEST['platform']} not found";   
    die(json_encode($obj));
}

if(empty($platform['isActive'])){
    $obj->msg = "Platform is not active";   
    die(json_encode($obj));
}

$url = preg_replace('/[^a-z0-9_\/@.:?&=;%-]/i', '', @$_POST['val']);

if(!empty($url) && !isValidURL($url)){
    $obj->msg = "URL is invalid {$_POST['url']} = {$url}";   
    die(json_encode($obj));
}

$cobj = AVideoPlugin::getObjectData("CustomizeUser");

$user = new User(User::getId());
$obj->added = $user->addExternalOptions($_REQUEST['platform'], $url);

$obj->error = empty($obj->added);

die(json_encode($obj));