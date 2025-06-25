<?php
require_once '../../../videos/configuration.php';

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
setRowCount(100);
$video = new Video('', '', $videos_id);
setDefaultSort('created', 'DESC');
$obj = new stdClass();
$obj->msg = '';
$obj->videos_id = $videos_id;
$obj->response = Ai_responses::getAllImageFromVideo($videos_id);
$obj->error = empty($obj->response) && !is_array($obj->response);
$obj->images = Video::listAllImagesInVideoLib($videos_id);

echo _json_encode($obj);
