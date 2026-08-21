<?php
$configFile = __DIR__ . '/../../videos/configuration.php';
require_once $configFile;
header('Content-Type: application/json');

// Validate required parameters
if (empty($_REQUEST['notifyCode']) || empty($_REQUEST['notify'])) {
    forbiddenPage('Missing required parameters');
}

$notifyCode = decryptString($_REQUEST['notifyCode']);
$notify = json_decode($_REQUEST['notify'], true);

// notifyCode is minted as encryptString(time()) in buildFFMPEGRemoteURL() - it must decrypt to a
// recent timestamp, not merely to something non-empty, otherwise any ciphertext ever issued by
// this install (e.g. a video_id_hash) would be accepted as proof the caller is the encoder.
define('NOTIFY_FFMPEG_MAX_AGE', 86400); // encoding jobs can be long-running

if (empty($notifyCode) || empty($notify) || !is_numeric($notifyCode) || abs(time() - intval($notifyCode)) > NOTIFY_FFMPEG_MAX_AGE) {
    forbiddenPage('Invalid parameters');
}

_error_log("notify.ffmpeg start " . json_encode($_REQUEST));

// Process video file and callback
$response = processNotifyVideoFile($notify);
$callback = decryptString($_REQUEST['callback'] ?? '');
if (!empty($callback)) {
    $result = processFFMPEGCallback($callback, $notify);
    if ($result) $response['callbackResult'] = $result;
}

echo json_encode($response, JSON_PRETTY_PRINT);
