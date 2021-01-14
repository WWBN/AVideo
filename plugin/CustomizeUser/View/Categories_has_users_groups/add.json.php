<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Categories_has_users_groups.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('CustomizeUser');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Categories_has_users_groups(@$_POST['id']);
$o->setCategories_id($_POST['categories_id']);
$o->setUsers_groups_id($_POST['users_groups_id']);
$o->setStatus($_POST['status']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
