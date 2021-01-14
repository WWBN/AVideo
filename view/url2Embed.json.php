<?php

if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->url = @$_REQUEST['url'];
$obj->embed = "";
$obj->playLink = "";
$obj->playEmbedLink = "";

if(isValidURL($obj->url)){
    $obj->msg = "URL is invalid";
    die(json_encode($obj));
}

$obj->embed = parseVideos($obj->url);

$obj->playLink = "{$global['webSiteRootURL']}evideo/".  encryptString(json_encode($obj->url));
$obj->playEmbedLink = "{$global['webSiteRootURL']}evideo/".  encryptString(json_encode($obj->embed));