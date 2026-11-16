<?php

require_once '../../../../videos/configuration.php';

_error_log('Restreamer get live keys start ' . json_encode($_REQUEST));
if (!AVideoPlugin::isEnabledByName('Live')) {
    forbiddenPage('Live plugin is disabled', true);
}

if (!Live::canRestream()) {
    forbiddenPage(__("You can not do this"));
}
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
header('Content-Type: application/json');

$byPassPermissionCheck = false;
if (!empty($_REQUEST['token'])) {
    $_REQUEST['live_restreams_id'] = Live::getRestreamsIdFromToken($_REQUEST['token']);
    $byPassPermissionCheck = true;
}
//var_dump($_REQUEST['token'], decryptString($_REQUEST['token']));exit;
if (empty($_REQUEST['live_restreams_id'])) {
    forbiddenPage('live_restreams_id cannot be empty', true);
}

$Live_restreams = new Live_restreams($_REQUEST['live_restreams_id']);

if (empty($Live_restreams->getName())) {
    forbiddenPage('Name not found for live_restreams_id=' . $_REQUEST['live_restreams_id'], true);
}

if (!$byPassPermissionCheck && $Live_restreams->getUsers_id() !== User::getId() && !User::isAdmin() && !isCommandLineInterface()) {
    forbiddenPage('You have no access to this restream', true);
}

$parameters = $Live_restreams->getParameters();
if (empty($parameters)) {
    _error_log('Restream parameters not present', AVideoLog::$WARNING);
}else{
    $parametersJson = json_decode($parameters);
}

if (empty($parametersJson) || empty($parametersJson->{'restream.ypt.me'})) {
    $response = new stdClass();
    $response->error = false;
    $response->msg = '';
    $response->stream_key = $Live_restreams->getStream_key();
    $response->stream_url = $Live_restreams->getStream_url();
    $response->provider = 'Local';
    $response->subtitle = $Live_restreams->getName();
    $response->http_code = 200;

    // Optional, reversible, admin-only diagnostic toggle to isolate whether a YouTube
    // destination's disconnects are specific to RTMPS vs RTMP, without touching encoding
    // parameters or any other destination. Off by default (empty string = no change).
    // Only wired into this "Local" (manually configured) provider branch: the automatic
    // restream.ypt.me-proxied branch below echoes a third-party response verbatim and is
    // intentionally left untouched here.
    require_once __DIR__ . '/../../standAloneFiles/restreamProfiles.php';
    if (getRestreamProvider($response->stream_url) === 'youtube') {
        $liveObj = AVideoPlugin::getObjectData('Live');
        if (!empty($liveObj->restreamForceProtocolForYouTubeTest)) {
            $response->forceProtocol = $liveObj->restreamForceProtocolForYouTubeTest;
        }
    }

    $json = json_encode($response);
    _error_log('Restreamer get live keys 1 ' . $json);
    echo $json;
} else {
    $lt = LiveTransmition::getFromDbByUser($Live_restreams->getUsers_id());

    $url = 'http://localhost/Restreamer/get.php';
    $url = 'http://127.0.0.1/Restreamer/get.php';
    if (empty($global['local_test_server'])) {
        $url = 'https://restream.ypt.me/get.php';
    }
    $array = array(
        'title' => $lt['title'],
        'description' => $lt['description'],
        'parameters64' => base64_encode(json_encode($parametersJson->{'restream.ypt.me'})),
        'poster_url' => Live::getPosterThumbsImage($lt['users_id'], $lt['live_servers_id']),
    );

    // Sent by restreamer.json.php (getLiveKeyBroadcastReuseQuery()); older restreamers do not
    // send it and keep getting a new broadcast every time.
    $liveTransmitionHistory_id = intval($_REQUEST['liveTransmitionHistory_id'] ?? 0);
    $checkBroadcast = isset($_REQUEST['checkYouTubeBroadcast']);
    if ($checkBroadcast) {
        $broadcastId = $_REQUEST['checkYouTubeBroadcast'];
        if (!isValidYouTubeBroadcastId($broadcastId) ||
            $broadcastId !== Live_restreams::getYouTubeBroadcastIdToReuse($Live_restreams->getId(), $liveTransmitionHistory_id)) {
            echo json_encode(['error' => true, 'msg' => 'Broadcast is no longer current']);
            exit;
        }
        $array['check_broadcast_id'] = $broadcastId;
        $url = str_replace('/get.php', '/youtube_broadcast_status.php', $url);
    } elseif (!empty($_REQUEST['reuseBroadcast']) && $liveTransmitionHistory_id > 0) {
        $reuseBroadcastId = Live_restreams::getYouTubeBroadcastIdToReuse($Live_restreams->getId(), $liveTransmitionHistory_id);
        if (!empty($reuseBroadcastId)) {
            $array['reuse_broadcast_id'] = $reuseBroadcastId;
        }
    }

    if (!empty($_REQUEST['live_schedule_id'])) {
        $ls = new live_schedule($_REQUEST['live_schedule_id']);

        if (!empty($ls->getTitle())) {
            $array['title'] = $ls->getTitle();
        }
        if (!empty($ls->getDescription())) {
            $array['description'] = $ls->getDescription();
        }

        $poster_url = $ls->getPosterURL(@$_REQUEST['live_schedule_id'], @$_REQUEST['ppv_schedule_id']);
        if (!empty($poster_url)) {
            $array['poster_url'] = $poster_url;
        }
    }

    $response = postVariables($url, $array, false);
    if (!$checkBroadcast) {
        _error_log("Restreamer get live keys 2 url={$url} response=[$response] ".json_encode($array));
        if ($liveTransmitionHistory_id > 0) {
            Live_restreams::saveYouTubeBroadcastFromRestreamerResponse($Live_restreams->getId(), $liveTransmitionHistory_id, $response);
        }
        $responseJson = is_string($response) ? json_decode($response) : null;
        if (getYouTubeBroadcastIdFromRestreamerResponse($responseJson) !== '' &&
            ($responseJson->broadcast_status_check_supported ?? false) === true) {
            // This marker must come from the Streamer, not just the broker: an older
            // Streamer would ignore checkYouTubeBroadcast and create another broadcast.
            $responseJson->broadcast_validation_supported = true;
            $response = json_encode($responseJson);
        }
    }
    echo $response;
}
 exit;
?>
