<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AuthorizeNet/Objects/Anet_webhook_log.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('AuthorizeNet');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Anet_webhook_log($id);
$obj->error = !$row->delete();
die(json_encode($obj));
?>