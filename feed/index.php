<?php 
//header("Content-Type: application/rss+xml; charset=UTF8");


require_once '../videos/configuration.php';
require_once '../objects/video.php';

$_POST['sort']["created"] = "DESC";
$_POST['current'] = 1;
$_REQUEST['rowCount'] = getRowCount();

$showOnlyLoggedUserVideos = false;
$title = $config->getWebSiteTitle();
$link = $global['webSiteRootURL'];
$logo = getCDN()."videos/userPhoto/logo.png";
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

$cacheName = "feedCache".json_encode($_GET);
$rows = ObjectYPT::getCache($cacheName, 0);
if(empty($rows)){
    // send $_GET['catName'] to be able to filter by category
    $rows = Video::getAllVideos("viewable", $showOnlyLoggedUserVideos);
    ObjectYPT::setCache($cacheName, $rows);
}else{
    $rows = object_to_array($rows);
}
if(!empty($_REQUEST['roku'])){
    include $global['systemRootPath'] . 'feed/roku.json.php';
}else if(empty($_REQUEST['mrss'])){
    include $global['systemRootPath'] . 'feed/rss.php';
}else{
    include $global['systemRootPath'] . 'feed/mrss.php';
}

function feedText($text){
    return str_replace(array('&&'), array('&'), str_replace(array('&','<','>'), array('&amp;','&lt;','&gt;'), (strip_tags(br2nl($text)))));
}

?>