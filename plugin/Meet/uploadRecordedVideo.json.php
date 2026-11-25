<?php
header('Content-Type: application/json');

if (!isset($global['systemRootPath'])) {
    error_log(__FILE__ . " line " . __LINE__);
    $configFile = __DIR__ . '/../../videos/configuration.php';
    error_log(__FILE__ . " line " . __LINE__);
    if (file_exists($configFile)) {
        error_log(__FILE__ . " line " . __LINE__);
        require_once $configFile;
    }
    error_log(__FILE__ . " line " . __LINE__);
}

error_log(__FILE__ . " line " . __LINE__ . ' REQUEST=' . json_encode($_REQUEST));
// Get all request headers
$headers = getallheaders();
$token = '';
// Check if the Authorization header exists
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    // Extract token after "Bearer "
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        _error_log(__FILE__ . " Token: Found");
    } else {
        _error_log(__FILE__ . " Invalid Authorization header format.");
    }
} else {
    _error_log(__FILE__ . " No Authorization header found.");
}

if (empty($token)) {
    forbiddenPage('Token not found');
}
error_log(__FILE__ . " line " . __LINE__);

$objM = AVideoPlugin::getObjectDataIfEnabled("Meet");

error_log(__FILE__ . " line " . __LINE__);
if (empty($objM)) {
    forbiddenPage('Plugin disabled');
}
error_log(__FILE__ . " line " . __LINE__);

if (!hash_equals($objM->secret, $token)) {
    forbiddenPage('Token does not match');
}
error_log(__FILE__ . " line " . __LINE__);

if (empty($_FILES['upl'])) {
    forbiddenPage('videoFile not found');
}
error_log(__FILE__ . " line " . __LINE__);

$users_id = (int) explode('-', $_FILES['upl']['name'])[0];
if ($users_id <= 0) {
    forbiddenPage('Invalid users_id in filename');
}

// Credit the upload to $users_id in-process without handing the caller a login.
// aVideoQueueEncoder.json.php needs $_SESSION['user'], but the session started by
// configuration.php is the caller's own (its cookie, or a PHPSESSID it chose), so
// writing the user there gave the caller a reusable session as that user, admin
// included (GHSA-qxvm-r42f-5p8j, GHSA-ggfr-w8qv-5cq3). Keep the caller's session
// untouched and run this request on a new server-generated session ID that is
// never sent back (no Set-Cookie). It still reopens after a mid-request close.
error_log(__FILE__ . " line " . __LINE__);
$userRow = User::getUserFromID($users_id);
// Only active accounts, as the former login(true, true) required.
if (empty($userRow['id']) || $userRow['status'] !== 'a') {
    forbiddenPage('Recording references unknown or inactive user');
}
_session_start();
// Some session handlers warn before returning false; keep the fallback silent.
$recordingUploadSessionId = @session_create_id();
if (empty($recordingUploadSessionId)) {
    $recordingUploadSessionId = bin2hex(random_bytes(32));
}
_session_write_close();
ini_set('session.use_cookies', '0');
session_id($recordingUploadSessionId);
_session_start();
$_SESSION = [];
$_SESSION['user'] = $userRow;
// getUserFromID() omits the credential that Video::queue() sends to the Encoder.
$_SESSION['user']['passhash'] = User::getUserHash($users_id);

$tmpFile = getTmpDir() . uniqid();

error_log(__FILE__ . " line " . __LINE__);
if (move_uploaded_file($_FILES['upl']['tmp_name'], $tmpFile)) {
    _error_log(__FILE__ . " including aVideoQueueEncoder filesize = " .  humanFileSize(filesize($tmpFile)));
    $_FILES['upl']['tmp_name'] = $tmpFile;
    _error_log(__FILE__ . " including aVideoQueueEncoder " .  json_encode($_FILES));
    require $global['systemRootPath'] . 'objects/aVideoQueueEncoder.json.php';
    _error_log(__FILE__ . " complete aVideoQueueEncoder ");
} else {
    _error_log(__FILE__ . " complete aVideoQueueEncoder ERROR on move file {$_FILES['upl']['tmp_name']} => $tmpFile");
}
