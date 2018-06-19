<?php
error_reporting(0);
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Content-type: text/plain');
$showOnlyLoggedUserVideos = true;
if (User::isAdmin()) {
    $showOnlyLoggedUserVideos = false;
}
$videos = Video::getAllVideos('', $showOnlyLoggedUserVideos, true);
foreach ($videos as $key => $value) {
    if(empty($_GET['type'])){
        echo Video::getPermaLink($videos[$key]['id']);
    }else{
        echo Video::getURLFriendly($videos[$key]['id']);
    }
    echo "\r\n";
}
