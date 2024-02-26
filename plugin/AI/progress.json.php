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
$aiURL = "{$aiURL}progress.json.php";

$json = array();
$json['AccessToken'] = $objAI->AccessToken;
$json['isTest'] = AI::$isTest?1:0;
//echo json_encode($obj);exit;

if (empty($json['AccessToken'])) {
    forbiddenPage('Invalid AccessToken');
}

$json['PlatformId'] = getPlatformId();
$json['videos_id'] = $videos_id;
$json['lang'] = $_REQUEST['lang'];
$json['type'] = $_REQUEST['type'];

$content = postVariables($aiURL, $json, false, 600);

if (empty($content)) {
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = "Oops! Our system took a bit longer than expected to process your request. 
    Please try again in a few moments. We apologize for any inconvenience and appreciate your patience.";
}

$jsonDecoded = json_decode($content);

if (empty($jsonDecoded)) {
    $jsonDecoded = new stdClass();
    $jsonDecoded->error = true;
    $jsonDecoded->msg = "Some how we got an error in the response";
    $jsonDecoded->content = $content;
}

$jsonDecoded->aiURL = $aiURL;

echo json_encode($jsonDecoded);
