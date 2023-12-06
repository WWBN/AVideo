<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$objAI = AVideoPlugin::getDataObjectIfEnabled('AI');

if (empty($objAI)) {
    forbiddenPage('AI plugin is disabled');
}

if (empty($objAI->AccessToken)) {
    forbiddenPage('Invalid AccessToken <br><a class="btn btn-primary btn-block" href="https://github.com/WWBN/AVideo/wiki/AI-Plugin#setting-up" target="_blank"><i class="fa-regular fa-circle-question"></i> Get help here<a>');
}

$videos_id = getVideos_id();

if (empty($videos_id)) {
    forbiddenPage('Videos ID is empty');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('Cannot edit this video');
}
_error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);

$param = array();
switch ($_REQUEST['type']) {
    case AI::$typeBasic:
        _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
        $obj = AI::getVideoBasicMetadata($videos_id);
        break;
    case AI::$typeTranscription:
        _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
        $obj = AI::getVideoTranscriptionMetadata($videos_id, $_REQUEST['language']);
        break;
    case AI::$typeTranslation:
        _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
        $obj = AI::getVideoTranslationMetadata($videos_id, $_REQUEST['lang'], $_REQUEST['langName']);
        $param['lang'] = $_REQUEST['lang'];
        break;
    default:
        _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
        forbiddenPage("Undefined type {$_REQUEST['type']}");
        break;
}

if ($obj->error) {
    _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
    forbiddenPage('Something happen: ' . $obj->msg);
}

$json = $obj->response;
$json['AccessToken'] = $objAI->AccessToken;
$json['isTest'] = AI::$isTest ? 1 : 0;
$json['webSiteRootURL'] = $global['webSiteRootURL'];
$json['PlatformId'] = getPlatformId();
$json['videos_id'] = $videos_id;
$json['type'] = $_REQUEST['type'];

_error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
$aiURLProgress = AI::getMetadataURL();
$aiURLProgress = "{$aiURLProgress}progress.json.php";

$content = postVariables($aiURLProgress, $json, false, 600);
if (empty($content)) {
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = "Could not post to {$aiURLProgress} => {$content}";
    
    die(json_encode($obj));
}
$jsonProgressDecoded = json_decode($content);
if (empty($jsonProgressDecoded)) {
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = "Could not decode => {$content}";
    
    die(json_encode($obj));
}
_error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
if (empty($jsonProgressDecoded->canRequestNew)) {
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = $jsonProgressDecoded->msg;
    $obj->jsonProgressDecoded = $jsonProgressDecoded;
    if(empty($obj->msg)){
        $obj->msg =  "A process for Video ID {$videos_id} is currently active and in progress.";;
    }

    die(json_encode($obj));
}
_error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);

$o = new Ai_responses(0);
$o->setVideos_id($videos_id);
$Ai_responses_id = $o->save();

$json['token'] = AI::getTokenForVideo($videos_id, $Ai_responses_id, $param);

$aiURL = AI::getMetadataURL();
$aiURL = "{$aiURL}async.json.php";
$content = postVariables($aiURL, $json, false, 600);
$jsonDecoded = json_decode($content);

_error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
if (empty($content)) {
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = "Oops! Our system took a bit longer than expected to process your request. 
    Please try again in a few moments. We apologize for any inconvenience and appreciate your patience.";
}

_error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
if (empty($jsonDecoded)) {
    $jsonDecoded = new stdClass();
    $jsonDecoded->error = true;
    $jsonDecoded->msg = "Some how we got an error in the response";
    $jsonDecoded->content = $content;
}

_error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
$jsonDecoded->aiURL = $aiURL;

$o = new Ai_responses($Ai_responses_id);
$o->setElapsedTime($jsonDecoded->elapsedTime);
$o->setPrice($jsonDecoded->payment->howmuch);
$jsonDecoded->Ai_responses = $o->save();

echo json_encode($jsonDecoded);
