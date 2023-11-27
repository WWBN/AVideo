<?php
//streamer config
require_once '../videos/configuration.php';
AVideoPlugin::loadPlugin('YPTStorage');
if (!isCommandLineInterface()) {
    return die('Command Line only');
}
error_reporting(E_ALL);
ini_set('display_errors', '1');

$videos_id = getVideos_id();

if(empty($videos_id)){
    die('No videos ID');
}

$resp = VideoStatistic::getLastStatistics(getVideos_id(), User::getId());

var_dump($resp);
