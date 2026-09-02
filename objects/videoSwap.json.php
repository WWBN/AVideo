<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$obj = new stdClass();
$obj->msg = '';
$obj->error = true;
$obj->confirmationRequired = false;

if (($advancedCustom->disableVideoSwap) || ($advancedCustom->makeSwapVideosOnlyForAdmin && !Permissions::canModerateVideos())) {
    $obj->msg = __("Swap Disabled");
    die(json_encode($obj));
}

if (!User::canUpload()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}
forbidIfIsUntrustedRequest('videoSwap');
$forceSwap = !empty($_POST['forceSwap']);
if ($forceSwap && !User::isAdmin()) {
    forbiddenPage('Only administrators can force a video swap', true);
}
if (empty($_POST['videos_id_1']) || empty($_POST['videos_id_2'])) {
    $obj->msg = __("Mou MUST select 2 videos to swap");
    die(json_encode($obj));
}

$video1 = new Video("", "", $_POST['videos_id_1']);
if (!$video1->userCanManageVideo()) {
    $obj->msg = __("You can not Manage This Video 1");
    die(json_encode($obj));
}

$video2 = new Video("", "", $_POST['videos_id_2']);
if (!$video2->userCanManageVideo()) {
    $obj->msg = __("You can not Manage This Video 2");
    die(json_encode($obj));
}

_error_log("Swap videos START: " . $video1->getId() . " with " . $video2->getId());
$video1Filename = $video1->getFilename();
$video1Sites_id = $video1->getSites_id();
$video1Duration = $video1->getDuration();
$video1Status = $video1->getStatus();

$video2Filename = $video2->getFilename();
$video2Sites_id = $video2->getSites_id();
$video2Duration = $video2->getDuration();
$video2Status = $video2->getStatus();

$unsafeSwapStatuses = [
    Video::STATUS_ENCODING,
    Video::STATUS_ACTIVE_AND_ENCODING,
    Video::STATUS_DOWNLOADING,
    Video::STATUS_TRANFERING,
];
$requiresForce = in_array($video1Status, $unsafeSwapStatuses, true) || in_array($video2Status, $unsafeSwapStatuses, true);
if ($requiresForce && !$forceSwap) {
    if (!User::isAdmin()) {
        $obj->msg = __("Only administrators can force a swap while a video is being processed");
        die(json_encode($obj));
    }
    $obj->confirmationRequired = true;
    $obj->msg = __("One of the selected videos is still being processed. Forcing the swap will also swap the video statuses, but the active Encoder job will remain linked to its original video ID and may overwrite files or change the status again. Cancel the Encoder job before continuing. Do you want to force the swap?");
    die(json_encode($obj));
}

$video1->setFilename($video2Filename, true);
$video1->setSites_id($video2Sites_id);
$video1->setDuration($video2Duration);

$video2->setFilename($video1Filename, true);
$video2->setSites_id($video1Sites_id);
$video2->setDuration($video1Duration);
mysqlBeginTransaction();
try {
    if (!$video1->save()) {
        throw new Exception("Error on swap video 1");
    }
    _error_log("Swap videos1 SUCCESS: " . $video1->getId());
    if (!$video2->save()) {
        throw new Exception("Error on swap video 2");
    }
    _error_log("Swap videos2 SUCCESS: " . $video2->getId());
    if ($requiresForce) {
        _error_log("Force swap videos by admin " . User::getId() . ": " . $video1->getId() . " status {$video1Status} with " . $video2->getId() . " status {$video2Status}", AVideoLog::$WARNING);
        if ($video1->setStatus($video2Status) === false || $video2->setStatus($video1Status) === false) {
            throw new Exception("Error on swap video status");
        }
    }
    $video1->setVideoHigestResolution(0);
    $video2->setVideoHigestResolution(0);
    if (!mysqlCommit()) {
        throw new Exception("Error on commit video swap");
    }
} catch (\Throwable $th) {
    mysqlRollback();
    $obj->msg = __($th->getMessage());
    _error_log("Swap videos ERROR: " . $th->getMessage(), AVideoLog::$ERROR);
    die(json_encode($obj));
}
_error_log("Swap videos END: " . $video1->getId() . " with " . $video2->getId());
$obj->error = false;
die(json_encode($obj));
