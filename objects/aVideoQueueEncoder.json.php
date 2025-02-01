<?php
header('Content-Type: application/json');

_error_log("AddQueueEncoder === Upload Script Started ===");

$obj = new stdClass();
$obj->error = true;
global $global, $config;

if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$global['bypassSameDomainCheck'] = 1;

_error_log("AddQueueEncoder Checking user permissions...");
if (!User::canUpload()) {
    $obj->msg = __("Permission denied");
    _error_log("AddQueueEncoder Permission denied for user: " . json_encode(User::getId()));
    die(json_encode($obj));
}

// Log uploaded file information
_error_log("AddQueueEncoder Received Files: " . print_r($_FILES, true));

$allowed = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'mp3', 'm4a', 'wav', 'm4v', 'webm', 'wmv'];

if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {
    _error_log("AddQueueEncoder File uploaded: " . $_FILES['upl']['name']);

    $updateVideoGroups = false;
    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
    _error_log("AddQueueEncoder Checking file extension: " . $extension);

    if (!in_array(strtolower($extension), $allowed)) {
        _error_log("AddQueueEncoder File extension error: " . $_FILES['upl']['name']);
        status(["status" => "error", "msg" => "File extension error (" . $_FILES['upl']['name'] . "), we allow only (" . implode(",", $allowed) . ")"]);
        exit;
    }

    $type = (in_array(strtolower($extension), ['mp3', 'wav', 'm4a'])) ? 'audio' : 'video';
    _error_log("AddQueueEncoder File type determined: " . $type);

    _error_log("AddQueueEncoder Extracting duration for: " . $_FILES['upl']['tmp_name']);
    $duration = Video::getDurationFromFile($_FILES['upl']['tmp_name']);
    _error_log("AddQueueEncoder Extracted Duration: " . $duration);

    // Check storage limits
    if (!empty($global['videoStorageLimitMinutes'])) {
        $maxDuration = $global['videoStorageLimitMinutes'] * 60;
        $currentStorageUsage = getSecondsTotalVideosLength();
        $thisFile = parseDurationToSeconds($duration);
        $limitAfterThisFile = $currentStorageUsage + $thisFile;

        _error_log("AddQueueEncoder Storage check: Max: $maxDuration, Current: $currentStorageUsage, File: $thisFile, After: $limitAfterThisFile");

        if ($maxDuration < $limitAfterThisFile) {
            _error_log("AddQueueEncoder Storage limit exceeded!");
            status(["status" => "error", "msg" => "Sorry, your storage limit has run out."]);
            exit;
        }
    }

    $path_parts = pathinfo($_FILES['upl']['name']);
    _error_log("AddQueueEncoder Filename parsed: " . print_r($path_parts, true));

    $mainName = preg_replace("/[^A-Za-z0-9]/", "", cleanString($path_parts['filename']));
    $paths = Video::getNewVideoFilename();
    $filename = $paths['filename'];
    $originalFilePath = Video::getStoragePath() . "original_" . $filename;

    _error_log("AddQueueEncoder Generated storage filename: " . $originalFilePath);

    $video = new Video(preg_replace("/_+/", " ", $path_parts['filename']), $filename, @$_FILES['upl']['videoId'], true);
    $video->setDuration($duration);
    $video->setType($type);
    $video->setStatus(Video::$statusEncoding);

    $id = $video->save($updateVideoGroups);
    _error_log("AddQueueEncoder Video saved with ID: " . $id);

    // Move/Copy file
    if (array_key_exists('copyOriginalFile', $_FILES['upl'])) {
        _error_log("AddQueueEncoder Copying file...");
        if (!copy($_FILES['upl']['tmp_name'], $originalFilePath)) {
            _error_log("AddQueueEncoder Copy failed: " . $_FILES['upl']['tmp_name'] . " -> " . $originalFilePath);
            die("Error on copy");
        }
    } elseif (array_key_exists('dontMoveUploadedFile', $_FILES['upl'])) {
        _error_log("AddQueueEncoder Renaming file...");
        if (!rename($_FILES['upl']['tmp_name'], $originalFilePath)) {
            _error_log("AddQueueEncoder Rename failed: " . $_FILES['upl']['tmp_name'] . " -> " . $originalFilePath);
            die("Error on rename");
        }
    } elseif (!move_uploaded_file($_FILES['upl']['tmp_name'], $originalFilePath)) {
        _error_log("AddQueueEncoder Moving file...");
        if (!rename($_FILES['upl']['tmp_name'], $originalFilePath)) {
            _error_log("AddQueueEncoder Move failed: " . $_FILES['upl']['tmp_name'] . " -> " . $originalFilePath);
            die("Error on move_uploaded_file");
        }
    }

    // Send video to encoding queue
    _error_log("AddQueueEncoder Adding video to encoding queue...");
    $queue = [];
    $queue[] = $video->queue([]);

    _error_log("AddQueueEncoder Encoding queue: " . json_encode($queue));
    status(["status" => "success", "msg" => "Your video ($filename) is queued", "filename" => "$filename", "duration" => "$duration", "queue" => json_encode($queue)]);
} else {
    _error_log("AddQueueEncoder File upload failed: " . print_r($_FILES, true));
    status(["status" => "error", "msg" => print_r($_FILES, true), "type" => '$_FILES Error']);
}
