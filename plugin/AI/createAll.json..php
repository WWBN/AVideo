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

$objSchedule = AVideoPlugin::getDataObjectIfEnabled('Scheduler');

if (empty($objSchedule)) {
    forbiddenPage('Scheduler is required');
}

$obj = Ai_scheduler::saveToProcessAll($videos_id, User::getId());

echo json_encode($obj);
