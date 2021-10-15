<?php
require_once '../../videos/configuration.php';
header('Content-Type: application/json');
        
$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

$objAutoPostOnSocialMedia = AVideoPlugin::getDataObjectIfEnabled('AutoPostOnSocialMedia');
if(empty($objAutoPostOnSocialMedia)){
    $obj->msg = 'Plugin disabled';
    die(json_encode($obj));
}

if((!isCommandLineInterface() && !User::isAdmin()) || isTokenValid($_REQUEST['token'])){
    $obj->msg = 'Forbbiden';
    die(json_encode($obj));
}
$video = AutoPostOnSocialMedia::getRandomVideo();

$obj->video = $video;
if(empty($obj->video)){
    $obj->msg = 'Invalid video';
    die(json_encode($obj));
}

$_REQUEST['videos_id'] = $obj->video['id'];


require_once $global['systemRootPath'] . 'plugin/AutoPostOnSocialMedia/post.json.php';

        