<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->token = @$_REQUEST['token'];

if (!AVideoPlugin::isEnabledByName('Live')) {
    $obj->msg = "Plugin is disabled";
    die(json_encode($obj));
}

if (empty($_REQUEST['token'])) {
    $obj->msg = "Token is empty";
    die(json_encode($obj));
}

$array = Live::decryptHash($_REQUEST['token']);

if (!is_array($array)) {
    $obj->msg = "Token is invalid";
    die(json_encode($obj));
}

$obj->users_id = intval($array['users_id']);

$twelveHours = 43200;

if (!empty($array['time']) && time() - $array['time'] > $twelveHours) {
    $obj->msg = "Token is expired";
    die(json_encode($obj));
}

$liveObj = AVideoPlugin::getDataObject('Live');

_error_log("Live::verifyToken.json.php {$_SERVER['HTTP_REFERER']} ". json_encode($array), AVideoLog::$DEBUG, true);

$trasnmition = LiveTransmition::createTransmitionIfNeed($obj->users_id);
$obj->key = $trasnmition['key'].'_'.time();
$lso = new LiveStreamObject($obj->key);
$obj->RTMPLinkWithOutKey = $lso->getRTMPLinkWithOutKey();
$obj->restreamStandAloneFFMPEG = $liveObj->restreamStandAloneFFMPEG ;

// Thread the admin-configured FIFO output-recovery settings down to the standalone
// restreamer.json.php the exact same way $restreamStandAloneFFMPEG above already does -
// sanitized/bounded here (see restreamProfiles.php's sanitizeRestreamFifoConfig()) so the
// standalone side never has to trust this response as already-safe (it re-sanitizes anyway).
require_once __DIR__ . '/standAloneFiles/restreamProfiles.php';
$obj->restreamFifo = sanitizeRestreamFifoConfig(array(
    'enabled' => !empty($liveObj->restreamFifoEnabled),
    'allowedProviders' => $liveObj->restreamFifoAllowedProviders,
    'attemptRecovery' => $liveObj->restreamFifoAttemptRecovery,
    'recoverAnyError' => $liveObj->restreamFifoRecoverAnyError,
    'restartWithKeyframe' => $liveObj->restreamFifoRestartWithKeyframe,
    'dropPktsOnOverflow' => $liveObj->restreamFifoDropPktsOnOverflow,
    'recoveryWaitTime' => $liveObj->restreamFifoRecoveryWaitTime,
    'recoveryWaitStreamtime' => $liveObj->restreamFifoRecoveryWaitStreamtime,
    'queueSize' => $liveObj->restreamFifoQueueSize,
    'maxRecoveryAttempts' => $liveObj->restreamFifoMaxRecoveryAttempts,
));

$obj->error = false;
die(json_encode($obj));
