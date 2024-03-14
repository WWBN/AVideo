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

if(!AI::canUseAI()){
    forbiddenPage('You cannot use AI');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

$obj = new stdClass();
$obj->videos_id = $videos_id;

$paths = convertVideoToMP3FileIfNotExists($videos_id);
$obj->path = str_replace('.mp3', '_Low.mp3', $paths['path']);;
$obj->error = !unlink($obj->path);
if($obj->error){
    $obj->msg = "Error on delete file";
}else{
    $obj->msg = "File deleted"; 
}

echo json_encode($obj);
