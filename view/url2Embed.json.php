<?php

if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->url = @$_REQUEST['url'];
$obj->embed = "";
$obj->playLink = "";
$obj->playEmbedLink = "";

if (!isValidURL($obj->url)) {
    $obj->msg = "URL is invalid";
    die(json_encode($obj));
}

$obj->error = false;
$obj->embed = parseVideos($obj->url, 1);


$evideo = new stdClass();
$evideo->videos_id = 0;
$evideo->videoLink = $obj->url;
$evideo->title = "";
$evideo->description = "";
$evideo->webSiteRootURL = $global['webSiteRootURL'];
$evideo->thumbnails = false;
$evideo->poster = false;
$evideo->filename = "";
$evideo->type = 'embed';
$evideo->users_id = User::getId();
$evideo->thumbnails = false;
$evideo->thumbnails = false;

$obj->playLink = "{$global['webSiteRootURL']}evideo/" . encryptString(json_encode($evideo));
$obj->playEmbedLink = "{$global['webSiteRootURL']}evideoEmbed/" . encryptString(json_encode($evideo));

die(json_encode($obj));
