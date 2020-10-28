<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Permissions/Objects/Users_groups_permissions.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Permissions');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Users_groups_permissions(@$_POST['id']);
$o->setName($_POST['name']);
$o->setusers_groups_id($_POST['users_groups_id']);
$o->setPlugins_id($_POST['plugins_id']);
$o->setType($_POST['type']);
$o->setStatus($_POST['status']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
