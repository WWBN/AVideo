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

$obj = AI::getVideoBasicMetadata($videos_id);

if ($obj->error) {
    forbiddenPage($obj->msg);
}

$json = $obj->response;

//echo json_encode($json);exit;

$content = postVariables(AI::getPricesURL(), $json, false, 60);

if(empty($content)){
    $obj = new stdClass();
    $obj->error = true;
    //$obj->url = AI::getPricesURL();
    //$obj->json = $json;
    $obj->content = $content;
    $obj->message = "Oops! Our system took a bit longer than expected to process your request. 
    Please try again in a few moments. We apologize for any inconvenience and appreciate your patience.";
    $content = json_encode($obj);
}

echo ($content);
