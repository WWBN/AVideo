<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$objAI = AVideoPlugin::getDataObjectIfEnabled('AI');

if (empty($objAI)) {
    forbiddenPage('AI plugin is disabled');
}

$videos_id = getVideos_id();

if (empty($videos_id)) {
    forbiddenPage('Videos ID is empty');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('Cannot edit this video');
}

$aiURL = AI::getMetadataURL();
$aiURL = "{$aiURL}async.json.php";
$param = array();
if (!_empty($_REQUEST['translation'])) {
    $obj = AI::getVideoTranslationMetadata($videos_id, $_REQUEST['lang']);
    $param['lang'] = $_REQUEST['lang'];
} else if (_empty($_REQUEST['transcription'])) {
    $obj = AI::getVideoBasicMetadata($videos_id);
} else {
    $obj = AI::getVideoTranscriptionMetadata($videos_id);
}

if ($obj->error) {
    forbiddenPage($obj->msg);
}

$json = $obj->response;
$json['AccessToken'] = $objAI->AccessToken;
//echo json_encode($obj);exit;

if (empty($json['AccessToken'])) {
    forbiddenPage('Invalid AccessToken');
}


$o = new Ai_responses(0);
$o->setVideos_id($videos_id);
$Ai_responses_id = $o->save();

$json['token'] = AI::getTokenForVideo($videos_id, $Ai_responses_id, $param);

$content = postVariables($aiURL, $json, false, 600);
$jsonDecoded = json_decode($content);

if (empty($content)) {
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = "Oops! Our system took a bit longer than expected to process your request. 
    Please try again in a few moments. We apologize for any inconvenience and appreciate your patience.";
}

if (empty($jsonDecoded)) {
    $jsonDecoded = new stdClass();
    $jsonDecoded->error = true;
    $jsonDecoded->msg = "Some how we got an error in the response";
    $jsonDecoded->content = $content;
}

//$jsonDecoded->lines = array();
//$jsonDecoded->json = $json;
$jsonDecoded->aiURL = $aiURL;


$o = new Ai_responses($Ai_responses_id);
$o->setElapsedTime($jsonDecoded->elapsedTime);
$o->setPrice($jsonDecoded->payment->howmuch);
$jsonDecoded->Ai_responses = $o->save();

echo json_encode($jsonDecoded);
