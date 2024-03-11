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

$objSchedule = AVideoPlugin::getDataObjectIfEnabled('Scheduler');

if (empty($objSchedule)) {
    forbiddenPage('Scheduler is required');
}

$obj = new stdClass();
$obj->videos_id = $videos_id;
$obj->users_id = User::getId();

$ai = new Ai_scheduler(0);
$ai->setAi_scheduler_type(Ai_scheduler::$typeProcessAll);
$ai->setJson($obj);
$ai->setStatus(Ai_scheduler::$statusActive);
$obj->schedulerSaved = $ai->save();
$obj->error = empty($obj->schedulerSaved );
if($obj->error){
    $obj->msg = _('Error');
}else{
    $obj->msg = _('Your video has been successfully scheduled for AI processing! You will receive notifications regarding the progress and completion.');
}

echo json_encode($obj);
