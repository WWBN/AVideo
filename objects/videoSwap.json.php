<?php

header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$obj = new stdClass();
$obj->msg = "";
$obj->error = true;

if (($advancedCustom->disableVideoSwap) || ($advancedCustom->makeSwapVideosOnlyForAdmin && !Permissions::canModerateVideos())) {
    $obj->msg = __("Swap Disabled");
    die(json_encode($obj));
}

if (!User::canUpload()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
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

$video1Filename = $video1->getFilename();
$video1Sites_id = $video1->getSites_id();
$video1Duration = $video1->getDuration();

$video2Filename = $video2->getFilename();
$video2Sites_id = $video2->getSites_id();
$video2Duration = $video2->getDuration();

$video1->setFilename($video2Filename, true);
$video1->setSites_id($video2Sites_id);
$video1->setDuration($video2Duration);

$video2->setFilename($video1Filename, true);
$video2->setSites_id($video1Sites_id);
$video2->setDuration($video1Duration);

$global['mysqli']->autocommit(false);
if(!$video1->save()){
    $obj->msg = __("Error on save video 1");
    die(json_encode($obj));
}
if(!$video2->save()){
    $obj->msg = __("Error on save video 2");
    die(json_encode($obj));
}
$global['mysqli']->commit();
$obj->error = false;
die(json_encode($obj));
