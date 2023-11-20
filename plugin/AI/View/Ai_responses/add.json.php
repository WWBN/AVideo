<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_responses.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('AI');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Ai_responses(@$_POST['id']);
$o->setElapsedTime($_POST['elapsedTime']);
$o->setVideos_id($_POST['videos_id']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
