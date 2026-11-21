<?php

// Which YouTube channel an existing destination sends to, for destinations connected before
// restream.ypt.me returned the channel. Read-only on YouTube; the answer is cached per destination.
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->channel = null;

if (!AVideoPlugin::isEnabledByName('Live') || !User::canStream()) {
    forbiddenPage('You cannot do this', true);
}

$live_restreams_id = intval($_REQUEST['live_restreams_id'] ?? 0);
$row = new Live_restreams($live_restreams_id);
if (empty($row->getId()) || (!User::isAdmin() && $row->getUsers_id() !== User::getId())) {
    forbiddenPage('You have no access to this restream', true);
}
_session_write_close();

$parameters = json_decode((string) $row->getParameters(), true);
if (LiveRestreamDestinationInfo::getConnectedProvider($parameters) !== 'youtube') {
    $obj->msg = 'Not a YouTube connection';
    die(json_encode($obj));
}

$channel = LiveRestreamDestinationInfo::getChannelFromParameters($parameters);
$status = Live_restreams::getDestinationStatus($live_restreams_id);
if (empty($channel)) {
    $channel = LiveRestreamDestinationInfo::normalizeChannel($status['channel'] ?? null);
}
if (empty($channel) && !empty($status['channel_lookup_failed_at']) && $status['channel_lookup_failed_at'] > time() - 3600) {
    $obj->msg = 'The channel could not be read recently, try again later';
    die(json_encode($obj));
}

if (empty($channel)) {
    $url = 'https://restream.ypt.me/youtube_channel_info.php';
    if (!empty($global['local_test_server'])) {
        $url = 'http://127.0.0.1/Restreamer/youtube_channel_info.php';
    }
    $response = postVariables($url, array(
        'parameters64' => base64_encode(json_encode($parameters['restream.ypt.me'])),
    ), false, 20);
    $json = is_string($response) ? json_decode($response, true) : null;
    $channel = is_array($json) && empty($json['error']) ? LiveRestreamDestinationInfo::normalizeChannel($json['channel'] ?? null) : null;
    if (empty($channel)) {
        _error_log("Live_restreams/channelInfo.json.php: could not read the channel of live_restreams_id={$live_restreams_id}", AVideoLog::$WARNING);
        Live_restreams::saveDestinationStatus($live_restreams_id, array('channel_lookup_failed_at' => time()));
        $obj->msg = 'Could not read the channel';
        die(json_encode($obj));
    }
    Live_restreams::saveDestinationStatus($live_restreams_id, array('channel' => $channel, 'channel_lookup_failed_at' => 0));
}

$obj->error = false;
$obj->channel = $channel;
$obj->channel_label = LiveRestreamDestinationInfo::getChannelLabel($channel);
die(json_encode($obj));
