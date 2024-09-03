<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/UserConnections/Objects/Users_connections.php';

$plugin = AVideoPlugin::loadPluginIfEnabled('UserConnections');
                                                
if(!User::isLogged()){
    forbiddenPage('Must Login');
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->users_id = intval($_REQUEST['users_id']);

if(empty($obj->users_id)){
    forbiddenPage('users_id is empty');
}

$obj->response = UserConnections::connectMe($obj->users_id);
$obj->error = empty($obj->response);

$obj->status = UserConnections::getMyConnectionStatus($obj->users_id);

echo json_encode($obj);
