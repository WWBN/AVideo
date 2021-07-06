<?php

if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

header('Content-Type: application/json');

$obj = new stdClass();
$obj->videos_id = "";
$obj->poster = "";
$obj->sources = "";
$obj->url = "";
$obj->friendly = "";
$obj->embed = "";
$obj->sprits = "";
$obj->nextURL = "";
$obj->nextURLEmbed = "";
$obj->error = true;
$obj->msg = "";
if (empty($_GET['url'])) {
    $obj->msg = "empty URL";
    die(json_encode($obj));
}
$obj->url = $_GET['url'];
$obj->vtt = array();

$patternURL = addcslashes($global['webSiteRootURL'], "/");
$obj->videos_id = getVideoIDFromURL($obj->url);

if (empty($obj->videos_id)) {
    $obj->msg = "videos_id NOT found";
    die(json_encode($obj));
}

$video = Video::getVideo($obj->videos_id);

if (empty($video['filename'])) {
    $obj->msg = "Video Not found";
    die(json_encode($obj));
}
if ($video['type'] !== 'video' && $video['type'] !== 'audio') {
    $obj->msg = "Must be a video [{$video['type']}]";
    die(json_encode($obj));
}

$obj->error = false;

$obj->friendly = Video::getURLFriendly($obj->videos_id);
$obj->embed = Video::getURLFriendly($obj->videos_id, true);
$obj->sources = getSources($video['filename'], true);
$obj->poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
$obj->sprits = "{$global['webSiteRootURL']}videos/{$video['filename']}_thumbsSprit.jpg";
$obj->title = $video['title'];
$obj->userPhoto = User::getPhoto($video['users_id']);

if (!empty($video['next_videos_id'])) {
    $obj->nextURL = Video::getURLFriendly($video['next_videos_id']);
    $obj->nextURLEmbed = Video::getURLFriendly($video['next_videos_id'], true);
}else{
    $catName = @$_GET['catName'];
    $cat = new Category($video['categories_id']);
    $_GET['catName'] = $cat->getClean_name();
    $next_video = Video::getVideo('', 'viewable', false, true);
    $_GET['catName'] = $catName;
    if (!empty($next_video['id'])) {
        $obj->nextURL = Video::getURLFriendly($next_video['id']);
        $obj->nextURLEmbed = Video::getURLFriendly($next_video['id'], true);
    }
}

if (function_exists('getVTTTracks')) {
    $obj->vtt = getVTTTracks($video['filename'], true);
}

die(json_encode($obj));
