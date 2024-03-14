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
$obj->lang = $global['langs_codes'][$_REQUEST['key']];
$paths = Ai_transcribe_responses::getVTTPaths($videos_id);
$fileToDelete = str_replace('.vtt', ".{$obj->lang['value']}.vtt", $paths['path']);
$obj->error = !unlink($fileToDelete);
if($obj->error){
    $obj->msg = "Error on delete {$obj->lang['value']}.vtt file";
}else{
    $obj->msg = "{$obj->lang['value']}.vtt file deleted"; 
}

echo json_encode($obj);
