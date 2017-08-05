<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

// pass admin user and pass
$user = new User("", @$_POST['user'], @$_POST['password']);
$user->login(false, true);
if (!User::isAdmin()) {
    $obj->msg = __("Permission denied to receive a file: ".  print_r($_POST, true));
    error_log($obj->msg);
    die(json_encode($obj));
}

// check if there is en video id if yes update if is not create a new one
$video = new Video("", "", @$_POST['videos_id']);
$obj->video_id = @$_POST['videos_id'];
$title = $video->getTitle();
if(empty($title) && !empty($_POST['title'])){
    $title = $video->setTitle($_POST['title']);
}else if(empty($title)){
    $video->setTitle("Automatic Title");
}
$video->setDuration($_POST['duration']);
// set active
$video->setStatus('a');

$video->setVideoDownloadedLink($_POST['videoDownloadedLink']);

if(preg_match("/(mp3|wav|ogg)$/i", $_POST['format'])){
    $type = 'audio';
    $video->setType($type);
}else if(preg_match("/(mp4|webm)$/i", $_POST['format'])){
    $type = 'video';
    $video->setType($type);
}

$videoFileName = $video->getFilename();
if(empty($videoFileName)){
    $mainName = preg_replace("/[^A-Za-z0-9]/", "", $title);
    $videoFileName = uniqid($mainName . "_YPTuniqid_", true);
    $video->setFilename($videoFileName);
}

// get video file from encoder
$destination = "{$global['systemRootPath']}videos/{$videoFileName}";
if(!empty($_FILES['video']['tmp_name'])){
    if(!move_uploaded_file ($_FILES['video']['tmp_name'] ,  "{$destination}.{$_POST['format']}")){
        $obj->msg = __("Could not move video file [{$_FILES['video']['tmp_name']}] => [{$destination}{$_POST['format']}]");
        error_log($obj->msg);
        die(json_encode($obj));
    }
}else{
    // set encoding
    $video->setStatus('e');
}
if(!empty($_FILES['image']['tmp_name']) && !file_exists("{$destination}.jpg")){
    if(!move_uploaded_file ($_FILES['image']['tmp_name'] ,  "{$destination}.jpg")){
        $obj->msg = __("Could not move image file [{$destination}.jpg]");
        error_log($obj->msg);
        die(json_encode($obj));
    }
}
if(!empty($_FILES['gifimage']['tmp_name']) && !file_exists("{$destination}.gif")){
    if(!move_uploaded_file ($_FILES['gifimage']['tmp_name'] ,  "{$destination}.gif")){
        $obj->msg = __("Could not move gif image file [{$destination}.gif]");
        error_log($obj->msg);
        die(json_encode($obj));
    }
}
$video_id = $video->save();


$obj->error = false;
$obj->video_id = $video_id;
error_log("Files Received");
die(json_encode($obj));

/*

error_log(print_r($_POST, true));
error_log(print_r($_FILES, true));
var_dump($_POST, $_FILES);

*/



