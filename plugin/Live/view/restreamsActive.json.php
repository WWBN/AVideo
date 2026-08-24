<?php

require_once __DIR__.'/../../../videos/configuration.php';

require_once __DIR__.'/../standAloneFiles/functions.php';

header('Content-Type: application/json');

$objP = AVideoPlugin::getDataObjectIfEnabled('Live');
if (empty($objP)) {
    $obj->msg = __('Live plugin is disabled');
    die(json_encode($obj));
}

if (!User::canStream() && !User::isAdmin()) {
    $obj->msg = __('Cannot stream');
    die(json_encode($obj));
}
$users_id = User::getId();

$obj = getRestreamsRuning();

$totalConnections = array();
foreach ($obj->process as $key => $value) {
    $processUsersId = LiveTransmition::getUsers_idOrCompanyFromKey($value['key']);

    // Non-admins may only see their own restreams, not every streamer's source key/identity.
    if (!User::isAdmin() && intval($processUsersId) !== intval($users_id)) {
        unset($obj->process[$key]);
        continue;
    }

    $lt = new Live_restreams($value['live_restreams_id']);
    $lth = new LiveTransmitionHistory($value['liveTransmitionHistory_id']);

    $obj->process[$key]['restream_name'] = $lt->getName();
    $obj->process[$key]['live_title'] = $lth->getTitle();
    $obj->process[$key]['identification'] = User::getNameIdentificationById($processUsersId);

    $obj->process[$key]['users_id'] = $processUsersId;
    // Count total connections per users_id
    if (!isset($totalConnections[$processUsersId])) {
        $totalConnections[$processUsersId] = 0;
    }
    $totalConnections[$processUsersId]++;
}
$obj->process = array_values($obj->process);
$obj->totalConnections = $totalConnections;
//$obj->isRestreamRuning = isRestreamRuning(2, 2);

die(json_encode($obj));

