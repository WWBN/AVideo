<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Emails_messages.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Scheduler');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Emails_messages(@$_POST['id']);
$o->setMessage($_POST['message']);
$o->setSubject($_POST['subject']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
