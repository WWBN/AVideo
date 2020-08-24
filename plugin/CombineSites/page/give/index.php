<?php

global $global, $config;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../../../../videos/configuration.php';
session_write_close();
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesDB.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesGive.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->evideos = array();
$obj->title = "";
$obj->image = "";
$obj->link = "";
if (empty($_REQUEST['site_url'])) {
    $obj->msg = "Empty site_url";
    die(json_encode($obj));
}

if (empty($_REQUEST['token'])) {
    $obj->msg = "Empty token";
    die(json_encode($obj));
}

$o = new CombineSitesDB(0);
$o->loadFromSite($_REQUEST['site_url']);

if (empty($o->getSite_url())) {
    $obj->msg = "Site not found";
    die(json_encode($obj));
}

if ($o->getStatus() !== 'a') {
    $obj->msg = "Site inactive";
    die(json_encode($obj));
}

if ($_REQUEST['token'] !== $o->getGive_token()) {
    $obj->msg = "Empty does not match";
    die(json_encode($obj));
}
$_POST['current'] = getCurrentPage();
$_REQUEST['rowCount'] = getRowCount(12);
$videos = array();
// verify if the site can access the requested content
if (!empty($_REQUEST['users_id'])) {
    $obj->title = User::getNameIdentificationById($_REQUEST['users_id']);
    $obj->image = "<img style='height: 15px;'  src='".User::getPhoto($_REQUEST['users_id'])."'>";
    $obj->link = User::getChannelLink($_REQUEST['users_id']);
    $videos = Video::getAllVideos("viewable", $_REQUEST['users_id']);
    // need to add dechex because some times it return an negative value and make it fails on javascript playlists
    //createGallerySection($videos);
} else
if (!empty($_REQUEST['categories_id'])) {
    $category = new Category($_REQUEST['categories_id']);
    $obj->title = $category->getName();
    $obj->image = "<i class='".$category->getIconClass()."'></i>";
    $obj->link = "{$global['webSiteRootURL']}cat/".$category->getClean_name();
    $_GET['catName'] = $category->getName();
    $videos = Video::getAllVideos("viewable");
    // need to add dechex because some times it return an negative value and make it fails on javascript playlists
    //createGallerySection($videos);
} else
if (!empty($_REQUEST['playlists_id'])) {
    $playList = new PlayList($_REQUEST['playlists_id']);
    $obj->title = $playList->getName();
    $obj->image = "<i class='fas fa-play-circle'></i>";
    $obj->link = "{$global['webSiteRootURL']}program/{$_REQUEST['playlists_id']}";
    $videosArrayId = PlayList::getVideosIdFromPlaylist($_REQUEST['playlists_id']);
    $videos = Video::getAllVideos("viewable", false, true, $videosArrayId, false, true);
    // need to add dechex because some times it return an negative value and make it fails on javascript playlists
    //createGallerySection($videos);
}
$obj->error = false;
foreach ($videos as $video) {
    $evideo = new stdClass();
    $evideo->videos_id = $video['id'];
    $evideo->videoLink = Video::getLink($video['id'], $video['clean_title']);
    $evideo->title = $video['title'];
    $evideo->description = "";
    $evideo->webSiteRootURL = $global['webSiteRootURL'];
    $evideo->images = Video::getImageFromFilename($video['filename']);
    $evideo->thumbnails = $evideo->images->thumbsJpg;
    $evideo->duration = $video['duration'];
    $evideo->views_count = $video['views_count'];
    $evideo->videoCreation = $video['videoCreation'];
    $evideo->trailer1 = $video['trailer1'];
    $evideo->creator = '<div class="col-xs-12 col-sm-12 col-md-12">
        <div class="pull-left">
                <img src="'.$config->getFavicon(true).'" alt="User Photo" class="img img-responsive img-circle zoom" style="max-width: 40px;">'
            . '</div>'
            . '<div class="commentDetails" style="margin-left:45px;">'
            . '<div class="commenterName text-muted">'
            . '<strong><a href="'.$evideo->videoLink.'" target="_blank" class="btn btn-xs btn-default"> '.$config->getWebSiteTitle().'</a></strong>'
            . '</div>'
            . '</div>'
            . '</div>';
    $obj->evideos[] = $evideo;
    
}
die(json_encode($obj));
?>