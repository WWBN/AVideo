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

if (empty($notifyCode) || empty($notify)) {
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
