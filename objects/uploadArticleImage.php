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
if (!Video::canEdit($_GET['video_id'])) {
    $obj->msg = 'You cant edit this file';
    die(json_encode($obj));
}
header('Content-Type: application/json');
// A list of permitted file extensions
$allowed = array('jpg', 'gif', 'png');
if (isset($_FILES['file_data']) && $_FILES['file_data']['error'] == 0) {
    $extension = pathinfo($_FILES['file_data']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), $allowed)) {
        $obj->msg = "File extension error [{$_FILES['file_data']['name']}], we allow only (" . implode(",", $allowed) . ")";
        die(json_encode($obj));
    }
    //var_dump($extension, $type);exit;
    $video = new Video("", "", $_GET['video_id']);
    if (!empty($video)) {
        $dir = "{$global['systemRootPath']}videos/articleImages/" . $video->getFilename()."/";
        $name = uniqid();
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        $destination = $dir . $name.".".strtolower($extension);
        error_log("Try to move " . $destination . " \n " . print_r($video, true));
        if (!move_uploaded_file($_FILES['file_data']['tmp_name'], $destination)) {
            $obj->msg = "Error on move_file_uploaded_file(" . $_FILES['file_data']['tmp_name'] . ", " . $destination;
            die(json_encode($obj));
        }
        $obj->url = "{$global['webSiteRootURL']}videos/articleImages/" . $video->getFilename()."/{$name}.".strtolower($extension);
        $obj->error = false;
    } else {
        $obj->msg = "Video Not found";
        die(json_encode($obj));
    }
}
$obj->msg = "\$_FILES Error";
$obj->FILES = $_FILES;
die(json_encode($obj));
