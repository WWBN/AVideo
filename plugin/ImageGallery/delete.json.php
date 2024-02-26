<?php

require_once '../../videos/configuration.php';
header('Content-Type: application/json');

if (!AVideoPlugin::isEnabledByName('ImageGallery')) {
    forbiddenPage('ImageGallery plugin is disabled');
}

$videos_id = getVideos_id();
ImageGallery::dieIfIsInvalid($videos_id);

$obj = new stdClass();
$obj->videos_id = $videos_id;
$obj->msg = '';
$obj->delete = ImageGallery::deleteFile($_REQUEST['filename'], $videos_id);
$obj->error = empty($obj->delete);
$obj->list = ImageGallery::listFiles($videos_id);

echo json_encode($obj);
?>