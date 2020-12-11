<?php 
//header("Content-Type: application/rss+xml; charset=UTF8");


require_once '../videos/configuration.php';
require_once '../objects/video.php';

$_POST['sort']["created"] = "DESC";
$_POST['current'] = 1;
$_REQUEST['rowCount'] = 50;

$showOnlyLoggedUserVideos = false;
$title = $config->getWebSiteTitle();
$link = $global['webSiteRootURL'];
$logo = "{$global['webSiteRootURL']}videos/userPhoto/logo.png";
$description = "";

$extraPluginFile = $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';
if (file_exists($extraPluginFile) && AVideoPlugin::isEnabledByName("Customize")) {
    require_once $extraPluginFile;
    $ec = new ExtraConfig();
    $description = $ec->getDescription();
}

if(!empty($_GET['channelName'])){
    $user = User::getChannelOwner($_GET['channelName']);
    $showOnlyLoggedUserVideos = $user['id'];
    $title = User::getNameIdentificationById($user['id']);
    $link = User::getChannelLink($user['id']);
    $logo = User::getPhoto($user['id']);
}

// send $_GET['catName'] to be able to filter by category
$rows = Video::getAllVideos("viewable", $showOnlyLoggedUserVideos);

if(!empty($_REQUEST['roku'])){
    header('Content-Type: application/json');
    include $global['systemRootPath'] . 'feed/roku.json.php';
}else if(empty($_REQUEST['mrss'])){
    header('Content-Type: text/xml; charset=UTF8');
    include $global['systemRootPath'] . 'feed/rss.php';
}else{
    header('Content-Type: text/xml; charset=UTF8');
    include $global['systemRootPath'] . 'feed/mrss.php';
}
?>