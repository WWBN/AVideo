<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/LoginLDAP/LoginLDAP.php';
$object = new stdClass();
$object->error = true;

if (!User::canUpload() && (empty($_GET['user']) || empty($_GET['pass']))) {
    $object->msg = "You need a user";
    die(json_encode($object));
}

$user = $_GET['user'];
$password = $_GET['pass'];

$userObj = new User(0, $user, $password);
$userObj->login(false, true);

if (!User::canUpload()) {
    $object->msg = "You can not upload";
    die(json_encode($object));
}

// A list of permitted file extensions
$allowed = array('mp4', 'avi', 'mov', 'mkv', 'flv', 'mp3', 'wav', 'm4v', 'webm', 'wmv');
error_log("MOBILE UPLOAD: Starts");
if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {

    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

    if (!in_array(strtolower($extension), $allowed)) {
        $object->msg = "File extension error (" . $_FILES['upl']['name'] . "), we allow only (" . implode(",", $allowed) . ")";
        error_log("MOBILE UPLOAD: {$object->msg}");
        die(json_encode($object));
    }
    //chack if is an audio
    $type = "video";
    if (strcasecmp($extension, 'mp3') == 0 || strcasecmp($extension, 'wav') == 0) {
        $type = 'audio';
    }

    require_once $global['systemRootPath'] . 'objects/video.php';
    $duration = Video::getDurationFromFile($_FILES['upl']['tmp_name']);

    // check if can upload video (about time limit storage)
    if (!empty($global['videoStorageLimitMinutes'])) {
        $maxDuration = $global['videoStorageLimitMinutes'] * 60;
        $currentStorageUsage = getSecondsTotalVideosLength();
        $thisFile = parseDurationToSeconds($duration);
        $limitAfterThisFile = $currentStorageUsage + $thisFile;
        if ($maxDuration < $limitAfterThisFile) {
            $object->msg = "Sorry, your storage limit has run out."
                    . "<br>[Max Duration: {$maxDuration} Seconds]"
                    . "<br>[Current Srotage Usage: {$currentStorageUsage} Seconds]"
                    . "<br>[This File Duration: {$thisFile} Seconds]"
                    . "<br>[Limit after this file: {$limitAfterThisFile} Seconds]";


            if (!empty($_FILES['upl']['videoId'])) {
                $video = new Video("", "", $_FILES['upl']['videoId']);
                $video->delete();
            }
            error_log("MOBILE UPLOAD: {$object->msg}");
            die(json_encode($object));
        }
    }

    $mainName = preg_replace("/[^A-Za-z0-9]/", "", cleanString($_FILES['upl']['name']));
    $filename = uniqid($mainName . "_YPTuniqid_", true);

    $video = new Video(preg_replace("/_+/", " ", $_FILES['upl']['name']), $filename, 0);
    $video->setDuration($duration);
    if ($type == 'audio') {
        $video->setType($type);
    } else {
        $video->setType("video");
    }
    $video->setStatus('e');

    if (!move_uploaded_file($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/original_" . $filename)) {
        $object->msg = "Error on move_uploaded_file(" . $_FILES['upl']['tmp_name'] . ", " . "{$global['systemRootPath']}videos/original_" . $filename . ")";
        error_log("MOBILE UPLOAD: {$object->msg}");
        die($object->msg);
    }

    $video->queue();

    $object->error = false;
    $object->msg = "We sent your video to the encoder";
    error_log("MOBILE SUCCESS UPLOAD: {$object->msg}");
    die(json_encode($object));
} else {
    error_log("MOBILE UPLOAD: File Not exists - " . print_r($_FILES, true));
}
