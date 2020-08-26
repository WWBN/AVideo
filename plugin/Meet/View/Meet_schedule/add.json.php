<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_schedule.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Meet');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Meet_schedule(@$_POST['id']);
$o->setUsers_id($_POST['users_id']);
$o->setStatus($_POST['status']);
$o->setPublic($_POST['public']);
$o->setLive_stream($_POST['live_stream']);
$o->setPassword($_POST['password']);
$o->setTopic($_POST['topic']);
$o->setStarts($_POST['starts']);
$o->setFinish($_POST['finish']);
$o->setName($_POST['name']);
$o->setMeet_code($_POST['meet_code']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
