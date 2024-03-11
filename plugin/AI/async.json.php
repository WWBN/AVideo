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

$response = AI::asyncVideosId($videos_id, $_REQUEST['type'], User::getId());
echo json_encode($response);
