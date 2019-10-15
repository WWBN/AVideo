<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

if (empty($_POST)) {
    $obj->msg = __("Your POST data is empty may be your vide file is too big for the host");
    error_log($obj->msg);
    die(json_encode($obj));
}
error_log("youPHPTubeEncoder.json: start");
if (empty($_POST['format']) || !in_array($_POST['format'], $global['allowedExtension'])) {
    error_log("youPHPTubeEncoder.json: Extension not allowed File " . __FILE__ . ": " . json_encode($_POST));
    die();
}
// pass admin user and pass
$user = new User("", @$_POST['user'], @$_POST['password']);
$user->login(false, true);
if (!User::canUpload()) {
    error_log("youPHPTubeEncoder.json: Permission denied to receive a file: " . json_encode($_POST));
    $obj->msg = __("Permission denied to receive a file: " . json_encode($_POST));
    error_log($obj->msg);
    die(json_encode($obj));
}

if (!empty($_POST['videos_id']) && !Video::canEdit($_POST['videos_id'])) {
    error_log("youPHPTubeEncoder.json: Permission denied to edit a video: " . json_encode($_POST));
    $obj->msg = __("Permission denied to edit a video: " . json_encode($_POST));
    error_log($obj->msg);
    die(json_encode($obj));
}

// check if there is en video id if yes update if is not create a new one
$video = new Video("", "", @$_POST['videos_id']);
$obj->video_id = @$_POST['videos_id'];
$title = $video->getTitle();
$description = $video->getDescription();
if (empty($title) && !empty($_POST['title'])) {
    $title = $video->setTitle($_POST['title']);
} elseif (empty($title)) {
    $video->setTitle("Automatic Title");
}

if (empty($description)) {
    $video->setDescription($_POST['description']);
}

$video->setDuration($_POST['duration']);

$status = $video->getStatus();
// if status is not unlisted
if ($status !== 'u' && $status !== 'a') {
    if (empty($advancedCustom->makeVideosInactiveAfterEncode)) {
        // set active
        $video->setStatus('a');
    } else {
        $video->setStatus('i');
    }
}
$video->setVideoDownloadedLink($_POST['videoDownloadedLink']);
error_log("youPHPTubeEncoder.json: Encoder receiving post " . json_encode($_POST));
//error_log(print_r($_POST, true));
if (preg_match("/(mp3|wav|ogg)$/i", $_POST['format'])) {
    $type = 'audio';
    $video->setType($type);
} elseif (preg_match("/(mp4|webm|zip)$/i", $_POST['format'])) {
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

if (!empty($_FILES)) {
    error_log("youPHPTubeEncoder.json: Files " . json_encode($_FILES));
} else {
    error_log("youPHPTubeEncoder.json: Files EMPTY");
}

if (!empty($_FILES['video']['error'])) {
    $phpFileUploadErrors = array(
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    );
    error_log("youPHPTubeEncoder.json: ********  Files ERROR " . $phpFileUploadErrors[$_FILES['video']['error']]);
    if(!empty($_POST['downloadURL']) && !empty($_FILES['video']['name'])){
        error_log("youPHPTubeEncoder.json: Try to download ".$_POST['downloadURL']);
        $file = url_get_contents($_POST['downloadURL']);
        error_log("youPHPTubeEncoder.json: Got the download ".$_POST['downloadURL']);
        if($file){
            $temp = "{$global['systemRootPath']}videos/cache/tmpFile/{$_FILES['video']['name']}";
            error_log("youPHPTubeEncoder.json: save ".$temp);
            make_path($temp);
            file_put_contents($temp, $file);
            $_FILES['video']['tmp_name'] = $temp;
        }
    }
}

// get video file from encoder
if (!empty($_FILES['video']['tmp_name'])) {
    $resolution = "";
    if (!empty($_POST['resolution'])) {
        $resolution = "_{$_POST['resolution']}";
    }
    $filename = "{$videoFileName}{$resolution}.{$_POST['format']}";
    error_log("youPHPTubeEncoder.json: receiving video upload to {$filename} " . json_encode($_FILES));
    decideMoveUploadedToVideos($_FILES['video']['tmp_name'], $filename);
} else {
    // set encoding
    $video->setStatus('e');
}
if (!empty($_FILES['image']['tmp_name']) && !file_exists("{$destination_local}.jpg")) {
    if (!move_uploaded_file($_FILES['image']['tmp_name'], "{$destination_local}.jpg")) {
        $obj->msg = print_r(sprintf(__("Could not move image file [%s.jpg]"), $destination_local), true);
        error_log("youPHPTubeEncoder.json: " . $obj->msg);
        die(json_encode($obj));
    }
}
if (!empty($_FILES['gifimage']['tmp_name']) && !file_exists("{$destination_local}.gif")) {
    if (!move_uploaded_file($_FILES['gifimage']['tmp_name'], "{$destination_local}.gif")) {
        $obj->msg = print_r(sprintf(__("Could not move gif image file [%s.gif]"), $destination_local), true);
        error_log("youPHPTubeEncoder.json: " . $obj->msg);
        die(json_encode($obj));
    }
}

if (!empty($_POST['categories_id'])) {
    $video->setCategories_id($_POST['categories_id']);
}

$video_id = $video->save();
$video->updateDurationIfNeed();
$video->updateHLSDurationIfNeed();

$obj->error = false;
$obj->video_id = $video_id;
error_log("youPHPTubeEncoder.json: Files Received for video {$video_id}: " . $video->getTitle());
die(json_encode($obj));

/*
error_log(print_r($_POST, true));
error_log(print_r($_FILES, true));
var_dump($_POST, $_FILES);
*/
