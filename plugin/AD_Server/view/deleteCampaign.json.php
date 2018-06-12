<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new VastCampaigns($id);
if($row->getUsers_id() == User::getId() || User::isAdmin()){
    $row->delete();
    $obj->error = false;
}
die(json_encode($obj));
?>