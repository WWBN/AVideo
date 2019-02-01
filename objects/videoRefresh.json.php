<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'video.php';
$obj = new Video("", "", $_POST['id']);
if(empty($obj)){
    die("Object not found");
}
$file = $global['systemRootPath']."videos/original_".$obj->getFilename();

if(!file_exists($file)){
    $file = $global['systemRootPath']."videos/".$obj->getFilename().".mp4";
    if(!file_exists($file)){
        $file = $global['systemRootPath']."videos/".$obj->getFilename().".mp3";
    }
}
if(file_exists($file)){
    $duration = Video::getDurationFromFile($file);
    $data = Video::getVideoConversionStatus($obj->getFilename());

    $obj->setDuration($duration);
    if($data->webm->progress == 100 && $data->mp4->progress == 100 && $obj->getStatus()!='i'){
        $obj->setStatus('a');
    }
}else{
    $obj->setStatus('i');
    $obj->setDuration('0:00:00.000000');
}
$resp = $obj->save();
$obj->updateDurationIfNeed();
echo '{"status":"'.!empty($resp).'"}';
