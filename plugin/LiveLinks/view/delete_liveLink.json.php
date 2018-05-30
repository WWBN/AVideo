<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = YouPHPTubePlugin::loadPluginIfEnabled('LiveLinks');

if(!$plugin->canAddLinks()){
    $obj->msg = "You cant delete links";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new LiveLinksTable($id);
if($row->getUsers_id() == User::getId() || User::isAdmin()){
    $row->delete();
    $obj->error = false;
}
die(json_encode($obj));
?>