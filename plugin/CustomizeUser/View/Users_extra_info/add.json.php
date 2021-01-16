<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_extra_info.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('CustomizeUser');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Users_extra_info(@$_POST['id']);
$o->setField_name($_POST['field_name']);
$o->setField_type($_POST['field_type']);
$o->setField_options($_POST['field_options']);
$o->setField_default_value($_POST['field_default_value']);
$o->setParameters($_POST['parameters']);
$o->setStatus($_POST['status']);
$o->setOrder($_POST['order']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
