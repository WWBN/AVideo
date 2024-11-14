<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/{pluginName}/Objects/{classname}.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('{pluginName}');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new {classname}(@$_POST['id']);
{columnsAdd}

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
