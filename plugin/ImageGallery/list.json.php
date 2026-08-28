<?php

require_once '../../videos/configuration.php';
header('Content-Type: application/json');


if (!AVideoPlugin::isEnabledByName('ImageGallery')) {
    forbiddenPage('ImageGallery plugin is disabled');
}

$videos_id = getVideos_id();
ImageGallery::dieIfIsInvalid($videos_id);

// same visibility/password rule enforced for the video page itself (CustomizeUser::getModeYouTube)
if (!User::canWatchVideoWithAds($videos_id)) {
    forbiddenPage('You cannot access this video');
}
$customizeUser = AVideoPlugin::loadPluginIfEnabled('CustomizeUser');
if (!empty($customizeUser) && !CustomizeUser::videoPasswordIsGood($videos_id)) {
    forbiddenPage('Video password required');
}

$obj = new stdClass();
$obj->videos_id = $videos_id;
$obj->error = false;
$obj->msg = '';
$obj->list = ImageGallery::listFiles($videos_id);

echo json_encode($obj);
?>