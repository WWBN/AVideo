<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideosStatistics/Objects/Statistics.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('VideosStatistics');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Statistics(@$_POST['id']);
$o->setUsers_id($_POST['users_id']);
$o->setTotal_videos($_POST['total_videos']);
$o->setTotal_video_views($_POST['total_video_views']);
$o->setTotal_subscriptions($_POST['total_subscriptions']);
$o->setTotal_comments($_POST['total_comments']);
$o->setTotal_likes($_POST['total_likes']);
$o->setTotal_dislikes($_POST['total_dislikes']);
$o->setTotal_duration_seconds($_POST['total_duration_seconds']);
$o->setCollected_date($_POST['collected_date']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
