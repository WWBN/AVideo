<?php
/**
 * Quick Go Live Endpoint
 * Handles one-click live session creation for WebRTC streaming
 */

header('Content-Type: application/json');

// Security: Initialize system
$responseError = [];
require_once __DIR__ . '/../../videos/configuration.php';

// Security: Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    forbiddenPage(__('Method not allowed'), true, '', '', '405 Method Not Allowed');
}

// Security: Check if user is logged in
if (!User::isLogged()) {
    forbiddenPage(__('You must be logged in to start a live session'), true, '', '', '401 Unauthorized');
}

// Security: Check if user has stream permission
if (!User::canStream()) {
    forbiddenPage(__('You do not have permission to start a live stream'), true);
}

// Load WebRTC plugin and check if Quick Go Live is enabled
AVideoPlugin::loadPlugin('WebRTC');
$webrtcPlugin = AVideoPlugin::getObjectDataIfEnabled('WebRTC');

if (empty($webrtcPlugin)) {
    forbiddenPage(__('WebRTC plugin is not enabled'), true, '', '', '503 Service Unavailable');
}

$webrtcConfig = AVideoPlugin::getDataObject('WebRTC');

// Check if Quick Go Live feature is enabled
if (empty($webrtcConfig->quickGoLiveEnabled)) {
    forbiddenPage(__('Quick Go Live feature is not enabled'), true);
}

// Ensure Live plugin is loaded and enabled
AVideoPlugin::loadPlugin('Live');
$livePlugin = AVideoPlugin::getObjectDataIfEnabled('Live');

if (empty($livePlugin)) {
    forbiddenPage(__('Live streaming plugin is not enabled'), true, '', '', '503 Service Unavailable');
}

try {
    $users_id = User::getId();

    // Check if WebRTC server is active
    if (!WebRTC::checkIfIsActive()) {
        _error_log('QuickGoLive: WebRTC server is not active, attempting to start...');
        WebRTC::startServer();

        // Give it a moment to start
        sleep(1);

        if (!WebRTC::checkIfIsActive()) {
            _error_log('QuickGoLive: Failed to start WebRTC server');
            forbiddenPage(__('WebRTC server is not available. Please contact the administrator.'), true, '', '', '503 Service Unavailable');
        }
    }

    // Check if user already has an active live session
    require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmitionHistory.php';
    $activeLive = LiveTransmitionHistory::getActiveLiveFromUser($users_id);

    if (!empty($activeLive)) {
        // User already has an active session, redirect them
        $liveUrl = $global['webSiteRootURL'] . 'plugin/WebRTC/index.php';

        $response = [
            'status' => 'success',
            'message' => __('You already have an active live session'),
            'liveUrl' => $liveUrl,
            'isExisting' => true
        ];

        echo json_encode($response);
        exit;
    }

    // Ensure user has a live transmission key
    $key = Live::getKeyFromUser($users_id);

    if (empty($key)) {
        _error_log('QuickGoLive: Failed to get or create live key for user ' . $users_id);
        forbiddenPage(__('Failed to initialize live session. Please try again.'), true, '', '', '500 Internal Server Error');
    }

    // Build the live URL
    $liveUrl = $global['webSiteRootURL'] . 'plugin/WebRTC/index.php';

    $response = [
        'status' => 'success',
        'message' => __('Live session ready'),
        'liveUrl' => $liveUrl,
        'key' => $key
    ];

    echo json_encode($response);

} catch (Exception $e) {
    _error_log('QuickGoLive: Exception - ' . $e->getMessage());
    forbiddenPage(__('An error occurred while starting the live session. Please try again.'), true, '', '', '500 Internal Server Error');
}
