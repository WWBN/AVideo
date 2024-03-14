<?php
require_once '../../videos/configuration.php';

header('Content-Type: application/json');

if (empty($_REQUEST['id'])) {
    forbiddenPage('ID is required');
}

if (empty($_REQUEST['ai_metatags_responses_id']) && empty($_REQUEST['ai_transcribe_responses_id'])) {
    forbiddenPage('ID is required');
}

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
$obj->error = true;
$obj->msg = array();
$obj->videos_id = $videos_id;
$obj->ai_transcribe_responses_id = @$_REQUEST['ai_transcribe_responses_id'];
$obj->ai_metatags_responses_id = @$_REQUEST['ai_metatags_responses_id'];
$obj->id = @$_REQUEST['id'];

if (!empty($_REQUEST['ai_transcribe_responses_id'])) {
    $aitr = new Ai_transcribe_responses($_REQUEST['ai_transcribe_responses_id']);
    $obj->Ai_responses = $aitr->getAi_responses_id();
    $ai = new Ai_responses($aitr->getAi_responses_id());
    if ($ai->getVideos_id() == $videos_id) {
        $obj->ai_transcribe_responses_id = $aitr->delete();
        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();
        $file = getVideosDir()."{$filename}/{$filename}_Low.mp3";
        unlink($file);
        
        $paths = Ai_transcribe_responses::getVTTPaths($videos_id, $aitr->getLanguage());
        unlink($paths['path']);

        $obj->error = empty($obj->ai_transcribe_responses_id);
    } else {
        $obj->msg[] = 'Invalid videos id for transcription '.$ai->getVideos_id();
    }
} else {
    $obj->msg[] = 'Empty transcription id ';
}

if (!empty($_REQUEST['ai_metatags_responses_id'])) {
    $aimr = new Ai_metatags_responses($_REQUEST['ai_metatags_responses_id']);
    $ai = new Ai_responses($aimr->getAi_responses_id());
    if ($ai->getVideos_id() == $videos_id) {
        $obj->ai_metatags_responses_id = $aimr->delete();
        $obj->error = empty($obj->ai_metatags_responses_id);
    } else {
        $obj->msg[] = 'Invalid videos id for metatags';
    }
} else {
    $obj->msg[] = 'Empty metatags id';
}


echo json_encode($obj);
