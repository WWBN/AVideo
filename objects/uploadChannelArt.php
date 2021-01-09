<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';

if(empty($_GET['video_id']) && !empty($_POST['videos_id'])){
    $_GET['video_id'] = $_POST['videos_id'];
}

$obj = new stdClass();
$obj->error = true;
if (!User::isLogged()) {
    $obj->msg = 'You cant edit this file';
    die(json_encode($obj));
}
header('Content-Type: application/json');
// A list of permitted file extensions
$allowed = array('jpg', 'jpeg', 'gif', 'png');
if (isset($_FILES['file_data']) && $_FILES['file_data']['error'] == 0) {
    $extension = pathinfo($_FILES['file_data']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), $allowed)) {
        $obj->msg = "File extension error [{$_FILES['file_data']['name']}], we allow only (" . implode(",", $allowed) . ")";
        die(json_encode($obj));
    }

    $tmpDestination = Video::getStoragePath()."userPhoto/tmp_background".User::getId().".". $extension;
    $obj->file = "videos/userPhoto/background".User::getId().".jpg";
    $oldfile = Video::getStoragePath()."userPhoto/background".User::getId().".png";

    if (!move_uploaded_file($_FILES['file_data']['tmp_name'], $tmpDestination)) {
        $obj->msg = "Error on move_file_uploaded_file(" . $_FILES['file_data']['tmp_name'] . ", " . Video::getStoragePath()."" . $filename . $ext;
        die(json_encode($obj));
    }
    convertImage($tmpDestination, $global['systemRootPath'].$obj->file, 70);
    unlink($tmpDestination);
    if(file_exists($oldfile)){
        unlink($oldfile);
    }

    echo "{}";
    exit;
}
$obj->msg = "\$_FILES Error";
$obj->FILES = $_FILES;
die(json_encode($obj));
