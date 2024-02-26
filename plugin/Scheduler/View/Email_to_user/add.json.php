<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Email_to_user.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Scheduler');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Email_to_user(@$_POST['id']);
$o->setSent_at($_POST['sent_at']);
$o->setTimezone($_POST['timezone']);
$o->setEmails_messages_id($_POST['emails_messages_id']);
$o->setUsers_id($_POST['users_id']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
