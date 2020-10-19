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

if (preg_match("/{$patternURL}v(ideo(Embed)?)?\/([0-9]+)/", $obj->url, $matches)) {
    if (empty($matches[3])) {
        $obj->msg = "videos_id NOT found";
        die(json_encode($obj));
    }
} else {
    $obj->msg = "it is not a valid URL";
    die(json_encode($obj));
}

$obj->videos_id = intval($matches[3]);

$video = Video::getVideo($obj->videos_id);

if (empty($video['filename'])) {
    $obj->msg = "Video Not found";
    die(json_encode($obj));
}
if ($video['type'] !== 'video') {
    $obj->msg = "Must be a video";
    die(json_encode($obj));
}

$obj->error = false;

$obj->friendly = Video::getURLFriendly($obj->videos_id);
$obj->embed = Video::getURLFriendly($obj->videos_id, true);
$obj->sources = getSources($video['filename'], true);
$obj->poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
$obj->sprits = "{$global['webSiteRootURL']}videos/{$video['filename']}_thumbsSprit.jpg";

if (!empty($video['next_videos_id'])) {
    $obj->nextURL = Video::getURLFriendly($video['next_videos_id']);
    $obj->nextURLEmbed = Video::getURLFriendly($video['next_videos_id'], true);
}

if (function_exists('getVTTTracks')) {
    $obj->vtt = getVTTTracks($video['filename'], true);
}

die(json_encode($obj));
