<?php
$configFile = '../../videos/configuration.php';
if (!file_exists($configFile)) {
    $configFile = '../videos/configuration.php';
}
require_once $configFile;
require_once $global['systemRootPath'] . 'objects/video.php';
$obj = new stdClass();
$obj->error = true;
if (!User::canUpload()) {
    $obj->msg = 'Only logged users can file_dataoad';
    die(json_encode($obj));
}
header('Content-Type: application/json');
// A list of permitted file extensions
$allowed = array('jpg', 'gif');
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
    }
    $obj->error = false;
    echo "{}";
    exit;
}
$obj->msg = "\$_FILES Error";
$obj->FILES = $_FILES;
die(json_encode($obj));
