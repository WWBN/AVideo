<?php
$configFile = '../../videos/configuration.php';
if (!file_exists($configFile)) {
    $configFile = '../videos/configuration.php';
}
$obj = new stdClass();
$obj->error = true;
require_once $configFile;
if (!User::canUpload()) {
    $obj->msg = "Only logged users can upload";
    die(json_encode($obj));
}
header('Content-Type: application/json');
// A list of permitted file extensions
$allowed = array('mp4');
if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {
    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), $allowed)) {
        $obj->msg = "File extension error [{$_FILES['upl']['name']}], we allow only (". implode(",", $allowed) . ")";
        die(json_encode($obj));
    }
    //var_dump($extension, $type);exit;
    require_once $global['systemRootPath'] . 'objects/video.php';
    $duration = Video::getDurationFromFile($_FILES['upl']['tmp_name']);
    $path_parts = pathinfo($_FILES['upl']['name']);
    $mainName = preg_replace("/[^A-Za-z0-9]/", "", $path_parts['filename']);
    $filename = uniqid($mainName . "_", true);
    $video = new Video(preg_replace("/_+/", " ", $_FILES['upl']['name']), $filename, @$_FILES['upl']['videoId']);
    $video->setDuration($duration);
    $video->setType("video");
    $video->setStatus('a');
    $id = $video->save();
    /**
     * This is when is using in a non uploaded movie
     */
    if (!move_uploaded_file($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/" . $filename.".mp4")) {
        $obj->msg = "Error on move_uploaded_file(" . $_FILES['upl']['tmp_name'] . ", " . "{$global['systemRootPath']}videos/" . $filename.".mp4)";
        die(json_encode($obj));
    }
    $obj->error = false;
    $obj->filename = $filename;
    $obj->duration = $duration;
    die(json_encode($obj));
}
$obj->msg = "\$_FILES Error";
$obj->FILES = $_FILES;
die(json_encode($obj));