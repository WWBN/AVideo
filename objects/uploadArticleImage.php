<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header('Content-Type: application/json');
require_once $global['systemRootPath'] . 'objects/video.php';

if (empty($_REQUEST['video_id']) && !empty($_POST['videos_id'])) {
    $_REQUEST['video_id'] = $_POST['videos_id'];
}

$obj = new stdClass();
$obj->error = true;
$obj->videos_id = intval($_REQUEST['video_id']);
if (!empty($obj->videos_id) && !Video::canEdit($obj->videos_id)) {
    $obj->msg = __("You can't edit this file");
    die(json_encode($obj));
}
// A list of permitted file extensions
$allowed = ['jpg', 'gif', 'png'];
if (isset($_FILES['file_data']) && $_FILES['file_data']['error'] == 0) {
    $extension = pathinfo($_FILES['file_data']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), $allowed)) {
        $obj->msg = "File extension error [{$_FILES['file_data']['name']}], we allow only (" . implode(",", $allowed) . ")";
        die(json_encode($obj));
    }
    $relativeDestinationDir = '';
    //var_dump($extension, $type);exit;
    if ($obj->videos_id > 0) {
        $video = new Video("", "", $obj->videos_id);
        if (!empty($video)) {
            $relativeDestinationDir = "articleImages" . DIRECTORY_SEPARATOR . $video->getFilename() . DIRECTORY_SEPARATOR;
        }
    }
    if (empty($relativeDestinationDir) && Permissions::canAdminUsers()) {
        $relativeDestinationDir = "videos" . DIRECTORY_SEPARATOR . "tmpAdminImages" . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
    }

    if (!empty($relativeDestinationDir)) {
        $name = uniqid();
        $filename = $name . "." . strtolower($extension);
        $relativeDestinationDirFilename = $relativeDestinationDir . $filename;
        $destinationDir = $global['systemRootPath'] . $relativeDestinationDir;
        make_path($destinationDir);
        $destinationDirFilename = $global['systemRootPath'] . $relativeDestinationDirFilename;
        _error_log("Try to move " . $destinationDirFilename);
        if (!move_uploaded_file($_FILES['file_data']['tmp_name'], $destinationDirFilename)) {
            $obj->msg = "Error on move_file_uploaded_file(" . $_FILES['file_data']['tmp_name'] . ", " . $destinationDirFilename;
            die(json_encode($obj));
        }
        $obj->url = getURL($relativeDestinationDirFilename);
        $obj->error = false;
    } else {
        $obj->msg = __("Error o save image");
        die(json_encode($obj));
    }
} else {
    $obj->msg = "\$_FILES Error";
}
$obj->FILES = $_FILES;
die(json_encode($obj));
