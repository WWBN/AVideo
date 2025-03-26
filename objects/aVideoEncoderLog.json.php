<?php
/*
error_log("avideoencoder REQUEST 1: " . json_encode($_REQUEST));
error_log("avideoencoder POST 1: " . json_encode($_REQUEST));
error_log("avideoencoder GET 1: " . json_encode($_GET));
*/
if (empty($global)) {
    $global = [];
}
$obj = new stdClass();
$obj->error = true;

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

inputToRequest();
/*
_error_log("REQUEST: " . json_encode($_REQUEST));
_error_log("POST: " . json_encode($_REQUEST));
_error_log("GET: " . json_encode($_GET));
*/
header('Content-Type: application/json');
allowOrigin();

$global['bypassSameDomainCheck'] = 1;
if (empty($_REQUEST)) {
    $obj->msg = ("Your POST data is empty, maybe your video file is too big for the host");
    _error_log($obj->msg);
    forbiddenPage($obj->msg);
}
//_error_log("aVideoEncoderLog.json: start");
_error_log("aVideoEncoderLog.json: start");
if (!isset($_REQUEST['encodedPass'])) {
    $_REQUEST['encodedPass'] = 1;
}
useVideoHashOrLogin();
if (!User::canUpload()) {
    $obj->msg = __("Permission denied to receive a file") . ': ' . json_encode($_REQUEST);
    _error_log("aVideoEncoderLog.json: {$obj->msg} canUploadMessage=[{$canUploadMessage}] " . json_encode(User::canNotUploadReason()));
    _error_log($obj->msg);
    forbiddenPage($obj->msg);
}
$obj->videos_id = intval($_REQUEST['videos_id']);
if (empty($obj->videos_id)) {
    $obj->msg = "Videos_id is required ";
    _error_log($obj->msg . json_encode($_REQUEST));

    forbiddenPage($obj->msg);
}

if (!Video::canEdit($obj->videos_id)) {
    $obj->msg = "You cannot edit videos_id  " . $obj->videos_id;
    _error_log($obj->msg . json_encode($_REQUEST));

    forbiddenPage($obj->msg);
}

_error_log("aVideoEncoderLog.json: start to receive: " . json_encode($_REQUEST));

// check if there is en video id if yes update if is not create a new one
$video = new Video("", "", $obj->videos_id, true);


$externalOptions = _json_decode($video->getExternalOptions());
if (empty($externalOptions)) {
    $externalOptions = new stdClass();
}
if (empty($externalOptions->encoderLog)) {
    $externalOptions->encoderLog = array();
}
$externalOptions->encoderLog[] = array('msg' => $_REQUEST['msg'], 'type' => $_REQUEST['type'], 'time' => time(), 'datetime'=>date('Y-m-d H:i:s'));

$video->setExternalOptions(json_encode($externalOptions));

$obj->saved = $video->save();

$obj->error = empty($obj->saved);

echo json_encode($obj);
