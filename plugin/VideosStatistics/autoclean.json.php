<?php
header('Content-Type: application/json');
require_once '../../videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPlugin('VideosStatistics');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$obj->error = empty(VideosStatistics::autoCleanStatisticsTable());

echo json_encode($obj);
