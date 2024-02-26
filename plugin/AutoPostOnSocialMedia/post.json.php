<?php
require_once '../../videos/configuration.php';
header('Content-Type: application/json');
        
$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->videos_id = 0;

$objAutoPostOnSocialMedia = AVideoPlugin::getDataObjectIfEnabled('AutoPostOnSocialMedia');
if(empty($objAutoPostOnSocialMedia)){
    $obj->msg = 'Plugin disabled';
    die(json_encode($obj));
}

if(!isCommandLineInterface() && !User::isAdmin() && !isTokenValid($_REQUEST['token'])){
    $obj->msg = 'Forbbiden';
    die(json_encode($obj));
}

$obj->videos_id = intval($_REQUEST['videos_id']);

if(empty($obj->videos_id)){
    $obj->videos_id = intval($argv[1]);
}

if(empty($obj->videos_id)){
    $obj->msg = 'Invalid videos ID';
    die(json_encode($obj));
}

$obj->response = AutoPostOnSocialMedia::postVideo($obj->videos_id);

if(!empty($obj->response->error)){
    $obj->msg = $obj->response->error;
}else{
    $obj->error = false;
}

die(json_encode($obj));