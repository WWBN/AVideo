<?php
require_once __DIR__ . '/../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');
if (empty($plugin)) {
    forbiddenPage('SocialMediaPublisher is disabled');
}

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

$obj->videos_id = getVideos_id();
if (empty($obj->videos_id)) {
    forbiddenPage('Videos Id cannot be empty');
}

if(!Video::canEdit($obj->videos_id)){
    forbiddenPage('You cannot edit this video');
}

$pref = new Publisher_user_preferences($row['id']);
if (YouTubePublisher::isYouTubeProvider($pref->getProviderName())) {
    // YouTube runs in the background: large files would time out inside this request
    try {
        $input = array(
            'title' => @$_REQUEST['title'],
            'description' => @$_REQUEST['description'],
            'tags' => @$_REQUEST['tags'],
            'categoryId' => @$_REQUEST['categoryId'],
            'privacy' => @$_REQUEST['visibility'],
        );
        $resp = YouTubePublisher::queue(User::getId(), $row['id'], $obj->videos_id, $input, !empty($_REQUEST['confirmDuplicate']));
        foreach ($resp as $key => $value) {
            $obj->$key = $value;
        }
    } catch (\Throwable $th) {
        _error_log('uploadVideo.json.php YouTube ' . get_class($th) . ': ' . $th->getMessage(), AVideoLog::$ERROR);
        $obj->error = true;
        $obj->msg = __('The upload could not be started. Please try again.');
    }
    die(json_encode($obj));
}

$obj->resp = SocialMediaPublisher::upload($row['id'], $obj->videos_id);
if(isset($obj->resp["error"])){
    $obj->error = $obj->resp["error"];
}else{
    $obj->error = empty($obj->resp);
}

$obj->msg = SocialUploader::getErrorMsg($obj->resp);
if ($obj->error && empty($obj->msg)) {
    $obj->msg = __('The video could not be published. Please try again later.');
}
// provider responses can include the access token (Instagram)
if (is_array($obj->resp)) {
    unset($obj->resp['accessToken']);
}

echo json_encode($obj);
