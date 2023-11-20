<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_transcribe_responses.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('AI');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Ai_transcribe_responses(@$_POST['id']);
$o->setAi($_POST['ai']);
$o->setVtt($_POST['vtt']);
$o->setLanguage($_POST['language']);
$o->setDuration($_POST['duration']);
$o->setText($_POST['text']);
$o->setTotal_price($_POST['total_price']);
$o->setSize_in_bytes($_POST['size_in_bytes']);
$o->setMp3_url($_POST['mp3_url']);
$o->setAi_responses_id($_POST['ai_responses_id']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
