<?php
require_once __DIR__ . '/../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');

if (!User::isLogged()) {
    forbiddenPage('You must login');
}

if (empty($_REQUEST['id'])) {
    forbiddenPage('Empty Publisher_user_preferences ID');
}
$obj->row = Publisher_user_preferences::getFromDb($_REQUEST['id']);

// check if there is Publisher_user_preferences
if(empty($obj->row)){
    forbiddenPage('There is no connection for provider '. $obj->provider['name']);
}

if(User::getId() != $obj->row['users_id']){
    forbiddenPage('Publisher_user_preferences does not belong to your user');
}

$obj->videos_id = getVideos_id();
if (empty($obj->videos_id)) {
    forbiddenPage('Videos Id cannot be empty');
}

if(!Video::canEdit($obj->videos_id)){
    forbiddenPage('You cannot edit this video');
}

$obj->resp = SocialMediaPublisher::upload($obj->row['id'], $obj->videos_id);
if(isset($obj->resp["error"])){
    $obj->error = $obj->resp["error"];
}else{
    $obj->error = empty($obj->resp);
}

$obj->msg = SocialUploader::getErrorMsg($obj->resp);

echo json_encode($obj);