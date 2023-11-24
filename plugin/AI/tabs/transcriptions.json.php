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

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}
setRowCount(100);
setDefaultSort('created', 'DESC');
$obj = new stdClass();
$obj->msg = '';
$obj->response = Ai_responses::getAllTranscriptionFromVideo($videos_id);


$paths = Ai_transcribe_responses::getVTTPaths($obj->videos_id);
$file = $paths['path'];
$obj->vttFileExists = file_exists($file) && filesize($file) > 20;

foreach ($obj->response as $key => $value) {
    $obj->response[$key]['size'] = humanFileSize($value['size_in_bytes']);
}

$obj->error = empty($obj->response) && !is_array($obj->response);
echo _json_encode($obj);