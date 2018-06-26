<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;

global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$_POST['videos_id'] = intval($_POST['videos_id']);

if (empty($_POST)) {
    $obj->msg = __("Your POST data is empty may be your vide file is too big for the host");
    error_log($obj->msg);
    die(json_encode($obj));
}

// pass admin user and pass
$user = new User("", @$_POST['user'], @$_POST['password']);
$user->login(false, true);
if (!User::canUpload()) {
    $obj->msg = __("Permission denied to Notify Done: " . print_r($_POST, true));
    error_log($obj->msg);
    die(json_encode($obj));
}

if(!Video::canEdit($_POST['videos_id'])){
    $obj->msg = __("Permission denied to edit a video: " . print_r($_POST, true));
    error_log($obj->msg);
    die(json_encode($obj));
}

// check if there is en video id if yes update if is not create a new one
$video = new Video("", "", $_POST['videos_id']);
$obj->video_id = $_POST['videos_id'];

if(empty($_POST['fail'])){
    $advancedCustom = YouPHPTubePlugin::getObjectDataIfEnabled("CustomizeAdvanced");
    if(empty($advancedCustom->makeVideosInactiveAfterEncode)){
        // set active
        $video->setStatus('a');
    }else{
        $video->setStatus('i');
    }
}else{
    $video->setStatus('i');
}
$video_id = $video->save();
if(empty($_POST['fail'])){
    YouPHPTubePlugin::afterNewVideo($_POST['videos_id']);
}
$obj->error = false;
$obj->video_id = $video_id;
error_log("Video is done notified {$video_id}: " . $video->getTitle());
die(json_encode($obj));

/*
error_log(print_r($_POST, true));
error_log(print_r($_FILES, true));
var_dump($_POST, $_FILES);
*/
