<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_schedule.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Publisher_schedule(@$_POST['id']);
$o->setScheduled_timestamp($_POST['scheduled_timestamp']);
$o->setStatus($_POST['status']);
$o->setTimezone($_POST['timezone']);
$o->setVideos_id($_POST['videos_id']);
$o->setUsers_id($_POST['users_id']);
$o->setPublisher_social_medias_id($_POST['publisher_social_medias_id']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
