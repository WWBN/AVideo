<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_extra_info.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->id = 0;

$plugin = AVideoPlugin::loadPluginIfEnabled('CustomizeUser');
                                                
if(!User::isLogged()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

if(empty($_POST['usersExtraInfo'])){
    $obj->msg = "Extra info is empty";
    die(json_encode($obj));
}

$obj->id = User::saveExtraInfo(json_encode($_POST['usersExtraInfo']), User::getId());

$obj->error = empty($obj->id);
echo json_encode($obj);