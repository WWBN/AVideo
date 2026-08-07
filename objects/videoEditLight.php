<?php
//error_reporting(0);
header('Content-Type: application/json');
require_once '../videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

// mutating endpoint, not named *.json.php so the auto CSRF guard never covers it; GET must not reach it
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $obj->msg = 'Method not allowed';
    die(json_encode($obj));
}
forbidIfIsUntrustedRequest('videoEditLight');

$obj->videos_id = @$_POST['videos_id'];

if (empty($obj->videos_id)) {
    $obj->msg = 'Videos ID empty';
    die(json_encode($obj));
}

if (!Video::canEdit($obj->videos_id)) {
    $obj->msg = 'You cannot edit this video';
    die(json_encode($obj));
}

$video = new Video('', '', $obj->videos_id);

if (isset($_POST['title'])) {
    $video->setTitle($_POST['title']);
    $video->setClean_title($_POST['title']);
}
if (isset($_POST['categories_id'])) {
    $video->setCategories_id($_POST['categories_id']);
}
if (isset($_POST['description'])) {
    $video->setDescription($_POST['description']);
}
if (isset($_POST['image'])) {
    $images = Video::getImageFromID($obj->videos_id);
    $video = new Video('', '', $obj->videos_id);
    $filename = $video->getFilename();
    // Get path directly for internal server-side use
    if (!empty($_POST['portrait'])) {
        $pathSource = Video::getSourceFile($filename, '_portrait.jpg', false, true);
        $path = $pathSource['path'];
    } else {
        $pathSource = Video::getSourceFile($filename, '.jpg', false, true);
        $path = $pathSource['path'];
    }
    if(ImagesPlaceHolders::isDefaultImage($path)){
        if (empty($_POST['portrait'])) {
            $pathSource = Video::getSourceFile($filename, '_portrait.jpg', false, true);
            $path = $pathSource['path'];
        } else {
            $pathSource = Video::getSourceFile($filename, '.jpg', false, true);
            $path = $pathSource['path'];
        }
    }
    if(ImagesPlaceHolders::isDefaultImage($path)){
        $path = "{$global['systemRootPath']}videos/{$$filename}/{$$filename}.jpg";
    }
    $obj->path = $path;
    $obj->image = saveCroppieImage($path, "image");
    $obj->deleteThumbs = Video::deleteThumbs($filename, true, false, true);
}
if(!empty($_POST['users_id'])){
    $userCanChangeVideoOwner = !empty($advancedCustomUser->userCanChangeVideoOwner) || Permissions::canAdminVideos();
    if($userCanChangeVideoOwner){
        $video->setUsers_id($_POST['users_id']);
    }
}

$obj->save = $video->save();
$obj->error = empty($obj->save);
if (empty($obj->error)) {
    if (isset($_POST['playlists_id'])) {
        if (!PlayLists::canAddVideoOnPlaylist($obj->save)) {
            Playlists::addVideo($obj->save, $_POST['playlists_id']);
        }
    }
    AVideoPlugin::saveVideosAddNew($_POST, $obj->videos_id);
    Video::clearCache($obj->videos_id, true);
}
die(json_encode($obj));
