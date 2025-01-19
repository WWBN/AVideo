<?php
//error_reporting(0);
header('Content-Type: application/json');
require_once '../videos/configuration.php';

$obj = new stdClass();
$obj->videos_id = @$_REQUEST['videos_id'];
$obj->error = true;
$obj->msg = '';

if (empty($obj->videos_id)) {
    $obj->msg = 'Videos ID empty';
    die(json_encode($obj));
}

if (!Video::canEdit($obj->videos_id)) {
    $obj->msg = 'You cannot edit this video';
    die(json_encode($obj));
}

$video = new Video('', '', $obj->videos_id);

if (isset($_REQUEST['title'])) {
    $video->setTitle($_REQUEST['title']);
    $video->setClean_title($_REQUEST['title']);
}
if (isset($_REQUEST['categories_id'])) {
    $video->setCategories_id($_REQUEST['categories_id']);
}
if (isset($_REQUEST['description'])) {
    $video->setDescription($_REQUEST['description']);
}
if (isset($_REQUEST['image'])) {
    $images = Video::getImageFromID($obj->videos_id);
    if (!empty($_REQUEST['portrait'])) {
        $path = $images->posterPortraitPath;
    } else {
        $path = $images->posterLandscapePath;
    }
    if(ImagesPlaceHolders::isDefaultImage($path)){
        if (empty($_REQUEST['portrait'])) {
            $path = $images->posterPortraitPath;
        } else {
            $path = $images->posterLandscapePath;
        }
    }
    if(ImagesPlaceHolders::isDefaultImage($path)){
        $fileName = $video->getFilename();
        $path = "{$global['systemRootPath']}videos/{$fileName}/{$fileName}.jpg";
    }
    $obj->path = $path;
    $obj->image = saveCroppieImage($path, "image");
}
if(!empty($_REQUEST['users_id'])){
    $userCanChangeVideoOwner = !empty($advancedCustomUser->userCanChangeVideoOwner) || Permissions::canAdminVideos();
    if($userCanChangeVideoOwner){
        $video->setUsers_id($_REQUEST['users_id']);
    }
}

$obj->save = $video->save();
$obj->error = empty($obj->save);
if (empty($obj->error)) {    
    if (isset($_REQUEST['playlists_id'])) {
        if (!PlayLists::canAddVideoOnPlaylist($obj->save)) {
            Playlists::addVideo($obj->save, $_REQUEST['playlists_id']);
        }
    }
    AVideoPlugin::saveVideosAddNew($_POST, $obj->videos_id);
    Video::clearCache($obj->videos_id, true);
}
die(json_encode($obj));
