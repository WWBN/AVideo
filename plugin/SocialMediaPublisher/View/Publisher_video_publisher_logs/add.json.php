<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_video_publisher_logs.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Publisher_video_publisher_logs(@$_POST['id']);
$o->setPublish_datetimestamp($_POST['publish_datetimestamp']);
$o->setStatus($_POST['status']);
$o->setDetails($_POST['details']);
$o->setVideos_id($_POST['videos_id']);
$o->setUsers_id($_POST['users_id']);
$o->setPublisher_social_medias_id($_POST['publisher_social_medias_id']);
$o->setTimezone($_POST['timezone']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
