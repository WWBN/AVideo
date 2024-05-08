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


// if there is check if still valid 
if($obj->row['accessTokenExpired']){
    // if is not valid try to revalidate SocialMediaPublisher::revalidateToken($_REQUEST['id']);
    $obj->revalidate = SocialMediaPublisher::revalidateTokenAndSave($obj->row['id']);
    if($obj->revalidate->error){
        forbiddenPage($obj->revalidate->msg);
    }else{
        // check if it is valid again
        $obj->row = Publisher_user_preferences::getFromUsersIdAndProvider(User::getId(), $obj->provider['name']);
    }
}

$obj->error = $obj->row['accessTokenExpired'];

die(json_encode($obj));
