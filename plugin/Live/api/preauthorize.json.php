<?php

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once dirname(__FILE__) . '/../Objects/LiveTransmition.php';
require_once dirname(__FILE__) . '/../Objects/StreamAuthCache.php';
require_once dirname(__FILE__) . '/../Live.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->rtmpUrl = "";
$obj->expiresIn = StreamAuthCache::getTTL();

// Log the request
_error_log("preauthorize.json.php - Request received from IP: " . getRealIpAddr());
_error_log("preauthorize.json.php - POST data: " . json_encode($_POST));

// Check if received user and pass via POST
if (empty($_POST['user']) || empty($_POST['pass'])) {
    $obj->msg = "Missing credentials";
    _error_log("preauthorize.json.php - Missing user or pass");
    die(json_encode($obj));
}

$username = $_POST['user'];
$password = $_POST['pass'];

// Attempt to login
try {
    $user = new User(0, $username, $password);

    if (empty($user->getBdId())) {
        $obj->msg = "Invalid credentials";
        _error_log("preauthorize.json.php - Invalid credentials for user: {$username}");
        die(json_encode($obj));
    }

    // Check if user can stream
    if (!$user->thisUserCanStream() && !User::isAdmin($user->getBdId())) {
        $obj->msg = "User not allowed to stream: " . User::getLastUserCanStreamReason();
        _error_log("preauthorize.json.php - User {$user->getBdId()} cannot stream: " . User::getLastUserCanStreamReason());
        die(json_encode($obj));
    }

    // Get user's LiveTransmition
    $liveTransmition = LiveTransmition::getFromDbByUser($user->getBdId());

    if (empty($liveTransmition) || empty($liveTransmition['key'])) {
        $obj->msg = "Stream key not found for user";
        _error_log("preauthorize.json.php - Stream key not found for user: {$user->getBdId()}");
        die(json_encode($obj));
    }

    $streamKey = $liveTransmition['key'];

    // Create temporary authorization (IP obtained internally)
    $authCreated = StreamAuthCache::create($streamKey, $user->getBdId());

    if (!$authCreated) {
        $obj->msg = "Failed to create authorization";
        _error_log("preauthorize.json.php - Failed to create auth for Key: {$streamKey}");
        die(json_encode($obj));
    }

    // Get RTMP URL
    $rtmpServer = Live::getServer();
    $rtmpUrl = rtrim($rtmpServer, '/') . '/' . $streamKey;

    // Success
    $obj->error = false;
    $obj->msg = "Authorized";
    $obj->rtmpUrl = $rtmpUrl;
    $obj->expiresIn = StreamAuthCache::getTTL();

    _error_log("preauthorize.json.php - Authorization created successfully for user {$user->getBdId()}, Key: {$streamKey}");

} catch (Exception $e) {
    $obj->msg = "Authentication error: " . $e->getMessage();
    _error_log("preauthorize.json.php - Exception: " . $e->getMessage());
}

// Clean up expired authorizations
StreamAuthCache::cleanup();

echo json_encode($obj);
