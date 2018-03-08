<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

if (empty($_POST)) {
    $obj->msg = __("Your POST data is empty may be your vide file is too big for the host");
    error_log($obj->msg);
    die(json_encode($obj));
}

if (empty($_POST['format']) || !in_array($_POST['format'], $global['allowedExtension'])) {
    error_log("Extension not allowed File " . __FILE__ . ": " . print_r($_POST, true));
    die();
}
// pass admin user and pass
$user = new User("", @$_POST['user'], @$_POST['password']);
$user->login(false, true);
if (!User::canUpload()) {
    $obj->msg = __("Permission denied to receive a file: " . print_r($_POST, true));
    error_log($obj->msg);
    die(json_encode($obj));
}

// check if there is en video id if yes update if is not create a new one
$video = new Video("", "", @$_POST['videos_id']);
$obj->video_id = @$_POST['videos_id'];
$title = $video->getTitle();
if (empty($title) && !empty($_POST['title'])) {
    $title = $video->setTitle($_POST['title']);
} elseif (empty($title)) {
    $video->setTitle("Automatic Title");
}
$video->setDuration($_POST['duration']);
$video->setDescription($_POST['description']);
// set active
$video->setStatus('a');

$video->setVideoDownloadedLink($_POST['videoDownloadedLink']);
error_log("Encoder receiving post");
error_log(print_r($_POST, true));
if (preg_match("/(mp3|wav|ogg)$/i", $_POST['format'])) {
    $type = 'audio';
    $video->setType($type);
} elseif (preg_match("/(mp4|webm)$/i", $_POST['format'])) {
    $type = 'video';
    $video->setType($type);
}

$videoFileName = $video->getFilename();
if (empty($videoFileName)) {
    $mainName = preg_replace("/[^A-Za-z0-9]/", "", cleanString($title));
    $videoFileName = uniqid($mainName . "_YPTuniqid_", true);
    $video->setFilename($videoFileName);
}


$destination_local = "{$global['systemRootPath']}videos/{$videoFileName}";
// get video file from encoder
if (!empty($_FILES['video']['tmp_name'])) {
    $resolution = "";
    if (!empty($_POST['resolution'])) {
        $resolution = "_{$_POST['resolution']}";
    }
    $filename = "{$videoFileName}{$resolution}.{$_POST['format']}";
    decideMoveUploadedToVideos($_FILES['video']['tmp_name'], $filename);
} else {
    // set encoding
    $video->setStatus('e');
}
if (!empty($_FILES['image']['tmp_name']) && !file_exists("{$destination_local}.jpg")) {
    if (!move_uploaded_file($_FILES['image']['tmp_name'], "{$destination_local}.jpg")) {
        $obj->msg = print_r(sprintf(__("Could not move image file [%s.jpg]"), $destination_local), true);
        error_log($obj->msg);
        die(json_encode($obj));
    } 
}
if (!empty($_FILES['gifimage']['tmp_name']) && !file_exists("{$destination_local}.gif")) {
    if (!move_uploaded_file($_FILES['gifimage']['tmp_name'], "{$destination_local}.gif")) {
        $obj->msg = print_r(sprintf(__("Could not move gif image file [%s.gif]"), $destination_local), true);
        error_log($obj->msg);
        die(json_encode($obj));
    } 
}
$video_id = $video->save();
$video->updateDurationIfNeed();

$obj->error = false;
$obj->video_id = $video_id;
error_log("Files Received for video {$video_id}: " . $video->getTitle());
die(json_encode($obj));

/*
error_log(print_r($_POST, true));
error_log(print_r($_FILES, true));
var_dump($_POST, $_FILES);
*/
