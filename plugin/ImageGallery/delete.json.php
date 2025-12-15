<?php

require_once '../../videos/configuration.php';
header('Content-Type: application/json');

if (!AVideoPlugin::isEnabledByName('ImageGallery')) {
    forbiddenPage('ImageGallery plugin is disabled');
}

if (!User::isLogged()) {
    forbiddenPage('You must be logged in to delete images');
}

$videos_id = getVideos_id();
ImageGallery::dieIfIsInvalid($videos_id);

$video = new Video('', '', $videos_id);
if (!$video->userCanManageVideo()) {
    forbiddenPage('You do not have permission to manage this video');
}

$obj = new stdClass();
$obj->videos_id = $videos_id;
$obj->msg = '';
$obj->delete = ImageGallery::deleteFile($_REQUEST['filename'], $videos_id);
$obj->error = empty($obj->delete);
$obj->list = ImageGallery::listFiles($videos_id);

echo json_encode($obj);
?>
