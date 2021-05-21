<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Scheduler_commands.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Scheduler');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Scheduler_commands(@$_POST['id']);
$o->setCallbackURL($_POST['callbackURL']);
$o->setParameters($_POST['parameters']);
$o->setDate_to_execute($_POST['date_to_execute']);
$o->setExecuted_in($_POST['executed_in']);
$o->setStatus($_POST['status']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
