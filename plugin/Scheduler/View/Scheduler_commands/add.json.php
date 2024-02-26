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
$o->setCallbackResponse($_POST['callbackResponse']);
$o->setTimezone($_POST['timezone']);
$o->setRepeat_minute($_POST['repeat_minute']);
$o->setRepeat_hour($_POST['repeat_hour']);
$o->setRepeat_day_of_month($_POST['repeat_day_of_month']);
$o->setRepeat_month($_POST['repeat_month']);
$o->setRepeat_day_of_week($_POST['repeat_day_of_week']);
$o->setType($_POST['type']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
