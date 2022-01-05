<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header('Content-Type: application/json');
$obj = new stdClass();
$obj->videos_id = intval($_REQUEST['videos_id']);
if (isset($_REQUEST['likes'])) {
    $obj->likes = intval($_REQUEST['likes']);
}
if (isset($_REQUEST['dislikes'])) {
    $obj->dislikes = intval($_REQUEST['dislikes']);
}
$obj->video_likes = 0;
$obj->video_dislikes = 0;
$obj->error = true;
$obj->msg = '';

if (empty($obj->videos_id)) {
    $obj->msg = 'invalid videos_id';
    die(json_encode($obj));
}

if (!Permissions::canAdminVideos()) {
    $obj->msg = 'Cannot admin videos';
    die(json_encode($obj));
}

if (isset($obj->likes)) {
    $obj->video_likes = Video::updateLikesDislikes($obj->videos_id, 'likes', $obj->likes);
}
if (isset($obj->dislikes)) {
    $obj->video_dislikes = Video::updateLikesDislikes($obj->videos_id, 'dislikes', $obj->dislikes);
}

$obj->error = false;
die(json_encode($obj));
