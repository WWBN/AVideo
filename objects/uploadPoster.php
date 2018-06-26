<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
$obj = new stdClass();
$obj->error = true;
if (!Video::canEdit($_GET['video_id'])) {
    $obj->msg = 'You cant edit this file';
    die(json_encode($obj));
}
header('Content-Type: application/json');
// A list of permitted file extensions
$allowed = array('jpg', 'gif');
if(!in_array(strtolower($_GET['type']), $allowed)){
    error_log("UploadPoster FIle extension not allowed");
    die();
}
if (isset($_FILES['file_data']) && $_FILES['file_data']['error'] == 0) {
    $extension = pathinfo($_FILES['file_data']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), $allowed)) {
        $obj->msg = "File extension error [{$_FILES['file_data']['name']}], we allow only (". implode(",", $allowed) . ")";
        die(json_encode($obj));
    }
    //var_dump($extension, $type);exit;
    $video = new Video("", "", $_GET['video_id']);
    /**
     * This is when is using in a non file_dataoaded movie
     */
    if (!move_uploaded_file($_FILES['file_data']['tmp_name'], "{$global['systemRootPath']}videos/" . $video->getFilename().".{$_GET['type']}")) {
        $obj->msg = "Error on move_file_dataoaded_file(" . $_FILES['file_data']['tmp_name'] . ", " . "{$global['systemRootPath']}videos/" . $filename.".{$_GET['type']})";
        die(json_encode($obj));
    }else{
        // delete thumbs from poster
        Video::deleteThumbs($video->getFilename());
    }
    $obj->error = false;
    echo "{}";
    exit;
}
$obj->msg = "\$_FILES Error";
$obj->FILES = $_FILES;
die(json_encode($obj));
