<?php
header('Content-Type: application/json');
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = YouPHPTubePlugin::loadPluginIfEnabled('LiveLinks');

if(!$plugin->canAddLinks()){
    $obj->msg = "You cant add links";
    die(json_encode($obj));
}

$o = new LiveLinksTable(@$_POST['linkId']);
$o->setDescription($_POST['description']);
$o->setEnd_date($_POST['end_date']);
$o->setLink($_POST['link']);
$o->setStart_date($_POST['start_date']);
$o->setStatus($_POST['status']);
$o->setTitle($_POST['title']);
$o->setType($_POST['type']);

if($o->save()){
    $obj->error = false;
}
echo json_encode($obj);
