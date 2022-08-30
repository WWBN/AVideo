<?php

require_once '../../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$objP = AVideoPlugin::getDataObjectIfEnabled('Live');
if (empty($objP)) {
    $obj->msg = __('Live plugin is disabled');
    die(json_encode($obj));
}

if (!User::canStream()) {
    $obj->msg = __('Cannot stream');
    die(json_encode($obj));
}

$lives = LiveTransmitionHistory::getAllActiveFromUser();
$restreamers = Live_restreams::getAllFromUser(User::getId());

foreach ($lives as $key => $value) {
    $lives[$key]['restream_log'] = array();
    foreach ($restreamers as $restream) {
        $log = Live_restreams_logs::getLatest($value['id'], $restream['id']);
        if (!empty($log)) {
            $lives[$key]['restream_log'][] = $log;
        }
    }
}

$obj->error = false;
$obj->lives = $lives;

die(json_encode($obj));
