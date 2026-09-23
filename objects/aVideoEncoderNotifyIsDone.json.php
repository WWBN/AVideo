<?php
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
allowOrigin();
inputToRequest();
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$global['bypassSameDomainCheck'] = 1;
$_REQUEST['videos_id'] = intval($_REQUEST['videos_id']);

if (empty($_REQUEST)) {
    $obj->msg = __("Your POST data is empty, maybe your video file is too big for the host");
    _error_log($obj->msg);
    die(json_encode($obj));
}

useVideoHashOrLogin();
if (!User::canUpload()) {
    $obj->code = 'streamer_access_denied';
    $obj->msg = __("The site refused access. Renew the encoder account access and check its upload permissions.");
    _error_log($obj->msg);
    die(json_encode($obj));
}

if (!Video::canEdit($_REQUEST['videos_id'])) {
    $obj->code = 'destination_unavailable';
    $obj->msg = __("The destination video was removed or this account cannot edit it. Check the video and account access on the site.");
    _error_log($obj->msg);
    die(json_encode($obj));
}

$file = getTmpDir("aVideoEncoderNotifyIsDone")."video_{$_REQUEST['videos_id']}";
// Storage hooks may outlive the encoder's HTTP connection. Finish the authorized
// operation so a later retry can read the successful completion marker.
ignore_user_abort(true);
set_time_limit(7200);
$lock = fopen($file . '.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    $obj->code = 'completion_in_progress';
    $obj->msg = __("Video completion is already in progress. Please retry shortly.");
    die(json_encode($obj));
}

try {
    // Older timestamp markers were written before processing and may represent a failed request.
    $completed = file_exists($file) ? json_decode(file_get_contents($file), true) : null;
    if (is_array($completed) && !empty($completed['completed'])) {
        $obj->error = false;
        $obj->video_id = $_REQUEST['videos_id'];
    } else {
        Video::clearCache($_REQUEST['videos_id'], true);
        // check if there is en video id if yes update if is not create a new one
        $video = new Video("", "", $_REQUEST['videos_id'], true);
        $obj->video_id = $_REQUEST['videos_id'];


        $video->setAutoStatus(Video::STATUS_ACTIVE);

        $video_id = $video->save();
        if (empty($video_id)) {
            throw new RuntimeException('Could not save the completed video');
        }

        $video = new Video("", "", $video_id, true);

        $obj->video_id = $video_id;
        Video::updateFilesize($video_id);
        // delete original files if any
        $originalFilePath =  Video::getStoragePath()."original_" . $video->getFilename();
        if (file_exists($originalFilePath)) {
            unlink($originalFilePath);
        }
        _error_log("Video is done notified {$video_id}: " . $video->getTitle());
        Video::clearCache($video_id, true);
        AVideoPlugin::onEncoderNotifyIsDone($video_id);
        AVideoPlugin::afterNewVideo($video_id);
        if (file_put_contents($file, json_encode(array('completed' => true, 'time' => time()))) === false) {
            throw new RuntimeException('Could not save the video completion marker');
        }
        $obj->error = false;
    }
} catch (Throwable $exception) {
    _error_log('Encoder completion failed for video ' . $_REQUEST['videos_id'] . ': ' . get_class($exception) . ': ' . $exception->getMessage());
    $obj->error = true;
    $obj->code = 'completion_failed';
    $obj->msg = __("Could not complete the video. Please retry.");
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
die(json_encode($obj));
