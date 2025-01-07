<?php
$configFile = __DIR__ . '/../../videos/configuration.php';
require_once $configFile;
header('Content-Type: application/json');

if (empty($_REQUEST['notifyCode'])) {
    forbiddenPage('Empty notifyCode');
}

$notifyCode = decryptString($_REQUEST['notifyCode']);
if (empty($notifyCode)) {
    forbiddenPage('Invalid notifyCode');
}

if (empty($_REQUEST['notify'])) {
    forbiddenPage('Empty notify');
}

$notify = json_decode($_REQUEST['notify'], true);
if (empty($notify)) {
    forbiddenPage('Invalid notify');
}

_error_log("notify.ffmpeg start ".json_encode($_REQUEST));

if (!empty($notify['avideoPath'])) {
    _error_log("notify.ffmpeg: Received notification for path: {$notify['avideoPath']}");

    $format = pathinfo($notify['avideoPath'], PATHINFO_EXTENSION);
    _error_log("notify.ffmpeg: File format detected: $format");

    if ($format == 'mp4' || $format == 'mp3') {
        _error_log("notify.ffmpeg: Format is valid for processing: $format");

        $obj = AVideoPlugin::getDataObjectIfEnabled('API');

        $localPath = "{$global['systemRootPath']}{$notify['avideoRelativePath']}";
        if(file_exists($localPath)){
            _error_log("notify.ffmpeg: this is a local call for ffmpeg, no download and not delete needed");
        }else if (!empty($obj) && !empty($obj->standAloneFFMPEG)) {
            _error_log("notify.ffmpeg: API Plugin and standAloneFFMPEG URL detected");

            $url = str_replace('plugin/API/standAlone/ffmpeg.json.php', '', $obj->standAloneFFMPEG);
            $url = "{$url}{$notify['avideoRelativePath']}";
            _error_log("notify.ffmpeg: Constructed URL: $url");

            $content = url_get_contents($url);
            if ($content === false) {
                _error_log("notify.ffmpeg: Failed to fetch content from URL: $url");
            } else {
                _error_log("notify.ffmpeg: Successfully fetched content. Content length: " . strlen($content));
            }
            if(!empty($content)){
                $filePath = "{$global['systemRootPath']}{$notify['avideoRelativePath']}";
                $bytes = file_put_contents($filePath, $content);
                if ($bytes === false) {
                    _error_log("notify.ffmpeg: Failed to save content to file: $filePath");
                } else {
                    _error_log("notify.ffmpeg: Successfully saved file. Bytes written: $bytes");
                }
            }else{
                _error_log("notify.ffmpeg: error, empty content");
            }
            
            _error_log("notify.ffmpeg: Attempting to delete remote folder for: {$notify['avideoFilename']}");
            $deleteStatus = deleteFolderFFMPEGRemote($notify['avideoFilename']);
            _error_log("notify.ffmpeg: Remote folder delete status: " . json_encode($deleteStatus));
        } else {
            _error_log("notify.ffmpeg: API Plugin or standAloneFFMPEG URL is not configured");
        }
    } else {
        _error_log("notify.ffmpeg: Unsupported file format: $format");
    }
} else {
    _error_log("notify.ffmpeg: No avideoPath provided in the notification");
}

// Collect and print JSON response with relevant information
$response = [
    'error' => empty($bytes),
    'avideoPath' => $notify['avideoPath'] ?? null,
    'format' => $format ?? null,
    'standAloneFFMPEG' => $obj->standAloneFFMPEG ?? null,
    'constructedURL' => $url ?? null,
    'contentLength' => isset($content) ? strlen($content) : null,
    'bytesWritten' => $bytes ?? null,
    'filePath' => $filePath ?? null,
    'deleteStatus' => $deleteStatus ?? null,
];

$callback = decryptString($_REQUEST['callback']);
if(!empty($callback)){
    _error_log("notify.ffmpeg: eval callback $callback");
    eval($callback);
}

echo json_encode($response, JSON_PRETTY_PRINT);
