<?php
require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$videos_id = getVideos_id();
if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

if (!isset($_REQUEST['startTimeInSeconds'])) {
    forbiddenPage('startTimeInSeconds is required');
}

if (!isset($_REQUEST['endTimeInSeconds'])) {
    forbiddenPage('endTimeInSeconds is required');
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

$objSchedule = AVideoPlugin::getDataObjectIfEnabled('Scheduler');

if (empty($objSchedule)) {
    forbiddenPage('Scheduler is required');
}

$obj = new stdClass();
$obj->videos_id = $videos_id;
$obj->startTimeInSeconds = $_REQUEST['startTimeInSeconds'];
$obj->endTimeInSeconds = $_REQUEST['endTimeInSeconds'];
$obj->users_id = User::getId();
$obj->description = $_REQUEST['description'];
$obj->aspectRatio = empty($_REQUEST['aspectRatio'])? Video::ASPECT_RATIO_ORIGINAL:$_REQUEST['aspectRatio'];
//$obj->title = $obj->aspectRatio.' '.$_REQUEST['title'];
$obj->title = $_REQUEST['title'];

$ai = new Ai_scheduler(0);
$ai->setAi_scheduler_type(Ai_scheduler::$typeCutVideo);
$ai->setJson($obj);
$ai->setStatus(Ai_scheduler::$statusActive);
$obj->schedulerSaved = $ai->save();

echo json_encode($obj);
