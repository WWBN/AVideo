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

if (empty($_POST)) {
    $obj->msg = __("Your POST data is empty, maybe your video file is too big for the host");
    _error_log("ReceiveImage: ".$obj->msg);
    die(json_encode($obj));
}

// pass admin user and pass
$user = new User("", @$_POST['user'], @$_POST['password']);
$user->login(false, true);
if (!User::canUpload()) {
    $obj->msg = __("Permission denied to receive a file: " . json_encode($_POST));
    _error_log("ReceiveImage: ".$obj->msg);
    die(json_encode($obj));
}

if(!Video::canEdit($_POST['videos_id'])){
    $obj->msg = __("Permission denied to edit a video: " . json_encode($_POST));
    _error_log("ReceiveImage: ".$obj->msg);
    die(json_encode($obj));
}
_error_log("ReceiveImage: "."Start receiving image ". json_encode($_FILES)."". json_encode($_POST));
// check if there is en video id if yes update if is not create a new one
$video = new Video("", "", $_POST['videos_id']);
$obj->video_id = $_POST['videos_id'];

_error_log("ReceiveImage: "."Encoder receiving post ". json_encode($_FILES));
//_error_log("ReceiveImage: ".json_encode($_POST));

$videoFileName = $video->getFilename();

$destination_local = Video::getStoragePath()."{$videoFileName}";

$obj->jpgDest = "{$destination_local}.jpg";
if (!empty($_FILES['image']['tmp_name']) && ( !empty($_REQUEST['update_video_id']) || !file_exists($obj->jpgDest) || filesize($obj->jpgDest)===42342)) {
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $obj->jpgDest)) {
        $obj->msg = print_r(sprintf(__("Could not move image file [%s.jpg]"), $destination_local), true);
        _error_log("ReceiveImage: ".$obj->msg);
        die(json_encode($obj));
    } else{
        $obj->jpgDestSize = humanFileSize(filesize($obj->jpgDest));
    }
}else{
    if(empty($_FILES['image']['tmp_name'])){
        _error_log("ReceiveImage: empty \$_FILES['image']['tmp_name'] " . json_encode($_FILES));
    }
    if(file_exists($obj->jpgDest)){
        _error_log("ReceiveImage: File already exists ".$obj->jpgDest);
        if(filesize($obj->jpgDest)!==42342){
            _error_log("ReceiveImage: file is not an error image ".filesize($obj->jpgDest));
        }
    }
}

if(!empty($_FILES['spectrumimage']['tmp_name'])){
    $obj->jpgSpectrumDest = "{$destination_local}_spectrum.jpg";
    if ((!empty($_REQUEST['update_video_id']) || !file_exists($obj->jpgSpectrumDest) || filesize($obj->jpgSpectrumDest)===42342)) {
        if (!move_uploaded_file($_FILES['spectrumimage']['tmp_name'], $obj->jpgSpectrumDest)) {
            $obj->msg = print_r(sprintf(__("Could not move image file [%s.jpg]"), $destination_local), true);
            _error_log("ReceiveImage: ".$obj->msg);
            die(json_encode($obj));
        } else{
            $obj->jpgSpectrumDest = humanFileSize(filesize($obj->jpgSpectrumDest));
        }
    }else{
        if(empty($_FILES['spectrumimage']['tmp_name'])){
            _error_log("ReceiveImage: empty \$_FILES['spectrumimage']['tmp_name'] " . json_encode($_FILES));
        }
        if(file_exists($obj->jpgSpectrumDest)){
            _error_log("ReceiveImage: File already exists ".$obj->jpgDest);
            if(filesize($obj->jpgSpectrumDest)!==42342){
                _error_log("ReceiveImage: file is not an error image ".filesize($obj->jpgDest));
            }
        }
    }
}

$obj->gifDest = "{$destination_local}.gif";
if (!empty($_FILES['gifimage']['tmp_name']) && (!empty($_REQUEST['update_video_id']) || !file_exists($obj->gifDest) || filesize($obj->gifDest)===2095341)) {
    if (!move_uploaded_file($_FILES['gifimage']['tmp_name'], $obj->gifDest)) {
        $obj->msg = print_r(sprintf(__("Could not move gif image file [%s.gif]"), $destination_local), true);
        _error_log("ReceiveImage: ".$obj->msg);
        die(json_encode($obj));
    } else{
        $obj->gifDestSize = humanFileSize(filesize($obj->gifDest));
    }
}else{
    if(empty($_FILES['gifimage']['tmp_name'])){
        _error_log("ReceiveImage: empty \$_FILES['gifimage']['tmp_name'] " . json_encode($_FILES));
    }
    if(file_exists($obj->gifDest)){
        _error_log("ReceiveImage: File already exists ".$obj->gifDest);
        if(filesize($obj->gifDest)!==42342){
            _error_log("ReceiveImage: file is not an error image ".filesize($obj->gifDest));
        }
    }
}
$obj->webpDest = "{$destination_local}.webp";
if (!empty($_FILES['webpimage']['tmp_name']) && (!empty($_REQUEST['update_video_id']) || !file_exists($obj->webpDest) || filesize($obj->webpDest)===2095341)) {
    if (!move_uploaded_file($_FILES['webpimage']['tmp_name'], $obj->webpDest)) {
        $obj->msg = print_r(sprintf(__("Could not move webp image file [%s.webp]"), $destination_local), true);
        _error_log("ReceiveImage: ".$obj->msg);
        die(json_encode($obj));
    } else{
        $obj->webpDestSize = humanFileSize(filesize($obj->webpDest));
    }
}else{
    if(empty($_FILES['webpimage']['tmp_name'])){
        _error_log("ReceiveImage: empty \$_FILES['webpimage']['tmp_name'] " . json_encode($_FILES));
    }
    if(file_exists($obj->webpDest)){
        _error_log("ReceiveImage: File already exists ".$obj->webpDest);
        if(filesize($obj->webpDest)!==42342){
            _error_log("ReceiveImage: file is not an error image ".filesize($obj->webpDest));
        }
    }
}

if(!empty($_REQUEST['duration'])){
    $duration = $video->getDuration();
    if(empty($duration) || $duration === 'EE:EE:EE'){
        $video->setDuration($_REQUEST['duration']);
    }
}

$videos_id = $video->save();
Video::clearCache($videos_id);
Video::updateFilesize($videos_id);
$obj->error = false;
$obj->video_id = $videos_id;

$json = json_encode($obj);
_error_log("ReceiveImage: "."Files Received for video {$videos_id}: " . $video->getTitle()." {$json}");
die($json);

/*
_error_log(json_encode($_POST));
_error_log(json_encode($_FILES));
var_dump($_POST, $_FILES);
*/
