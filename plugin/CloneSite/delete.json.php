<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/Objects/Clones.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = YouPHPTubePlugin::loadPluginIfEnabled('CloneSite');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Clones($id);
if(User::isAdmin()){
    $row->delete();
    $obj->error = false;
}
die(json_encode($obj));