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

$obj = getRestreamsRuning();

$totalConnections = array();
foreach ($obj->process as $key => $value) {
    $users_id = LiveTransmition::getUsers_idOrCompanyFromKey($value['key']);
    $obj->process[$key]['users_id'] = $users_id;
    // Count total connections per users_id
    if (!isset($totalConnections[$users_id])) {
        $totalConnections[$users_id] = 0;
    }
    $totalConnections[$users_id]++;
}
$obj->totalConnections = $totalConnections;
//$obj->isRestreamRuning = isRestreamRuning(2, 2);

die(json_encode($obj));

