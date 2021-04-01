<?php
header('Content-Type: application/json');
require_once '../../../videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->response = @$_REQUEST['response'];

$plugin = AVideoPlugin::loadPluginIfEnabled('LoginControl');
                                                
if(!User::isLogged()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}


$obj->error = empty(LoginControl::verifyChallenge($obj->response));

echo json_encode($obj);


?>
