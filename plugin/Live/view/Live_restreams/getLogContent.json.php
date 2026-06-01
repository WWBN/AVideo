<?php
/**
 * Returns the FFMPEG log content for a given restream session.
 * Accessible by the stream owner or any admin.
 * The request is proxied through AVideo so the restreamer can be on a different server.
 */
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams_logs.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error  = true;
$obj->msg    = '';
$obj->content = '';

if (!AVideoPlugin::isEnabledByName('Live')) {
    forbiddenPage('Plugin is disabled');
}

if (!Live::canRestream()) {
    forbiddenPage(__("You can not do this"));
}

$live_restreams_logs_id      = intval(@$_REQUEST['live_restreams_logs_id']);
$live_transmitions_history_id = intval(@$_REQUEST['live_transmitions_history_id']);
$live_restreams_id            = intval(@$_REQUEST['live_restreams_id']);

// Resolve from log record if provided
if (!empty($live_restreams_logs_id)) {
    $lrl = new Live_restreams_logs($live_restreams_logs_id);
    $live_transmitions_history_id = $lrl->getLive_transmitions_history_id();
    $live_restreams_id            = $lrl->getLive_restreams_id();
}

if (empty($live_transmitions_history_id) || empty($live_restreams_id)) {
    $obj->msg = __('Missing required parameters');
    die(json_encode($obj));
}

// Authorization: owner or admin
if (!User::isAdmin()) {
    $lr = new Live_restreams($live_restreams_id);
    if ($lr->getUsers_id() !== User::getId()) {
        forbiddenPage(__("You have no access to this restream"));
    }
}

$url = Live_restreams_logs::getURL(
    $live_transmitions_history_id,
    $live_restreams_id,
    $live_restreams_logs_id,
    'logContent'
);

if (empty($url)) {
    $obj->msg = __('Could not build log URL. The restreamer may not be configured.');
    die(json_encode($obj));
}

$response = url_get_contents($url);
$json = json_decode($response);

if (empty($json)) {
    $obj->msg = __('Could not reach the restreamer server.');
    die(json_encode($obj));
}

if (!empty($json->error)) {
    $obj->msg = $json->msg;
    die(json_encode($obj));
}

$obj->error     = false;
$obj->isActive  = !empty($json->isActive);
$obj->modified  = intval(@$json->modified);
$obj->secondsAgo = intval(@$json->secondsAgo);
$obj->remoteLog = !empty($json->remoteLog);
// Truncate to 500 KB max to prevent oversized responses
$rawContent = isset($json->content) ? $json->content : '';
$obj->content   = mb_substr($rawContent, -512000);

die(json_encode($obj));
