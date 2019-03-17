<?php
require_once '../../videos/configuration.php';
session_write_close();
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
if(!User::isLogged()){
    $obj->msg = "Not logged";   
    die(json_encode($obj));
}
if(empty($_POST['type'])){
    $obj->msg = "Type is empty";   
    die(json_encode($obj));
}
if(!isset($_POST['value'])){
    $obj->msg = "value is empty";   
    die(json_encode($obj));
}

$cu = YouPHPTubePlugin::loadPluginIfEnabled('CustomizeUser');

if(empty($cu)){
    $obj->msg = "Plugin not enabled";   
    die(json_encode($obj));
}

$obj->error = false;
switch ($_POST['type']) {
    case 'userCanAllowFilesDownload':
        CustomizeUser::setCanDownloadVideosFromUser(User::getId(), $_POST['value']=="true"?true:false);
        break;
    case 'userCanAllowFilesShare':
        CustomizeUser::setCanShareVideosFromUser(User::getId(), $_POST['value']=="true"?true:false);
        break;
}

die(json_encode($obj));