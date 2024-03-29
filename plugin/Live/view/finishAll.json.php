<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$objP = AVideoPlugin::getDataObjectIfEnabled('Live');
if (empty($objP)) {
    $obj->msg = __('Live plugin is disabled');
    die(json_encode($obj));
}

if (!User::isAdmin() && !isCommandLineInterface()) {
    $obj->msg = __('Not Admin');
    die(json_encode($obj));
}
if (!empty($_REQUEST['all'])) {
    $obj->error = !LiveTransmitionHistory::finishALL();
    if (empty($obj->error)) {
        $obj->msg = __('All lives were marked as finished');
    }
} else {
    $obj->error = false;
    $obj->finished = LiveTransmitionHistory::finishALLOffline();
    $obj->msg = count($obj->finished).' '.__('Lives finished');
}

die(json_encode($obj));
