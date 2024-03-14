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
$paths = Ai_transcribe_responses::getVTTPaths($videos_id);
//$obj->path = $paths['path'];
$pathsMP3 = convertVideoToMP3FileIfNotExists($videos_id);
$mp3 = str_replace('.mp3', '_Low.mp3', $pathsMP3['path']);
$mp3 = str_replace('.vtt', '.srt', $paths['path']);
$obj->unlinkMP3 = unlink($mp3);
$obj->unlinkVTT = unlink($paths['path']);
$obj->unlinkSRT = unlink($paths['path']);

$obj->error = file_exists($paths['path']) || file_exists($mp3);

if($obj->error){
    $obj->msg = "Error on delete file";
}else{
    $obj->msg = "File deleted"; 
}

echo json_encode($obj);
