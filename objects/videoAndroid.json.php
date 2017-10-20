<?php
require_once '../videos/configuration.php';
require_once 'video.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Content-Type: application/json');

$video = new Video("", "", $_POST['id']);
if(empty($video)){
    die("Object not found");
}
$video->addView();
echo json_encode($video);