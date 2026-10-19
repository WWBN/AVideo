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
$row = Publisher_user_preferences::getFromDb($_REQUEST['id']);

// check if there is Publisher_user_preferences
if(empty($row)){
    forbiddenPage('This connection does not exist');
}

if(User::getId() != $row['users_id']){
    forbiddenPage('Publisher_user_preferences does not belong to your user');
}


// if there is check if still valid
if($row['accessTokenExpired']){
    // if is not valid try to revalidate
    $revalidate = SocialMediaPublisher::revalidateTokenAndSave($row['id']);
    if($revalidate->error){
        forbiddenPage(__('The connection expired and could not be renewed. Please reconnect your account.'));
    }else{
        // check if it is valid again
        $row = Publisher_user_preferences::getFromDb($row['id'], true);
    }
}

$obj->error = $row['accessTokenExpired'];
// never send the tokens to the browser
$obj->row = SocialMediaPublisher::getPublicConnectionRow($row);

die(json_encode($obj));
