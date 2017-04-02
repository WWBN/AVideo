<?php
header('Content-Type: application/json');
require_once 'user.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'video.php';
$obj = new Video("", "", $_POST['id']);
if(empty($obj)){
    die("Object not found");
}
$file = $global['systemRootPath']."videos/original_".$obj->getFilename();

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
echo '{"status":"'.!empty($resp).'"}';