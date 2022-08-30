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

$live_restreams_logs_id = intval($_REQUEST['live_restreams_logs_id']);

if (empty($live_restreams_logs_id)) {
    forbiddenPage('live_restreams_logs_id is empty');
}

$url = Live_restreams_logs::getURL($live_restreams_logs_id, 'log');
$obj->log = json_decode(url_get_contents($url));

if(!empty($obj->log)){
    $obj->error = false;
}
        
die(json_encode($obj));
