<?php
require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$videos_id = getVideos_id();
if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

if (!AVideoPlugin::isEnabledByName('AI')) {
    forbiddenPage('AI plugin is disabled');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

$obj = new stdClass();
$obj->videos_id = $videos_id;
$paths = Ai_transcribe_responses::getVTTPaths($videos_id);
$obj->path = $paths['path'];
$obj->error = !unlink($paths['path']);
if($obj->error){
    $obj->msg = "Error on delete file";
}else{
    $obj->msg = "File deleted"; 
}

echo json_encode($obj);
