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
$obj->response = Ai_responses::getAllBasicFromVideo($videos_id);
$obj->error = empty($obj->response) && !is_array($obj->response);

echo _json_encode($obj);