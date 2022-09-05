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
$logo = getURL("videos/userPhoto/logo.png");
$description = '';

$extraPluginFile = $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';
if (file_exists($extraPluginFile) && AVideoPlugin::isEnabledByName("Customize")) {
    require_once $extraPluginFile;
    $ec = new ExtraConfig();
    $description = $ec->getDescription();
}

if (!empty($_GET['channelName'])) {
    $user = User::getChannelOwner($_GET['channelName']);
    $showOnlyLoggedUserVideos = $user['id'];
    $title = User::getNameIdentificationById($user['id']);
    $about = User::getDescriptionById($user['id'], true);
    if(!isHTMLEmpty($about)){
        $description = $about;
    }
    $link = User::getChannelLink($user['id']);
    $logo = User::getPhoto($user['id']);
}

$cacheName = "feedCache".json_encode($_GET);
$rows = ObjectYPT::getCache($cacheName, 0);
if (empty($rows)) {
    // send $_GET['catName'] to be able to filter by category
    $sort = @$_POST['sort'];
    if(empty($_REQUEST['program_id'])){
        if(empty($_POST['sort'])){
            $_POST['sort'] = array('created'=>'DESC');
        }
        $rows = Video::getAllVideos("viewable", $showOnlyLoggedUserVideos);
    }else{
        unset($_POST['sort']);
        $videosArrayId = PlayList::getVideosIdFromPlaylist($_REQUEST['program_id']);
        $rows = Video::getAllVideos("viewable", false, true, $videosArrayId, false, true);
        $rows = PlayList::sortVideos($rows, $videosArrayId);
        //var_dump($videosArrayId);foreach ($rows as $value) {var_dump($value['id']);}exit;
    }
    $_POST['sort'] = $sort;
    ObjectYPT::setCache($cacheName, $rows);
} else {
    $rows = object_to_array($rows);
}
if (!empty($_REQUEST['roku'])) {
    include $global['systemRootPath'] . 'feed/roku.json.php';
} elseif (empty($_REQUEST['mrss'])) {
    include $global['systemRootPath'] . 'feed/rss.php';
} else {
    include $global['systemRootPath'] . 'feed/mrss.php';
}

function feedText($text){
    return trim(str_replace(['&&'], ['&'], str_replace(['&nbsp;','&','<','>'], [' ','&amp;','&lt;','&gt;'], (strip_tags(br2nl($text))))));
}
