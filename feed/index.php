<?php

//header("Content-Type: application/rss+xml; charset=UTF8");

$global['ignoreUserMustBeLoggedIn'] = 1;
require_once '../videos/configuration.php';
require_once '../objects/video.php';
$global['ignoreUserMustBeLoggedIn'] = 1;
$_POST['sort']["created"] = "DESC";
$_POST['current'] = 1;
$_REQUEST['rowCount'] = getRowCount();

$advancedCustom = AVideoPlugin::getDataObject('CustomizeAdvanced');

if(!empty($advancedCustom->disableFeeds)){
    forbiddenPage('Feeds are disabled');
}

if(empty($config)){
    require_once $global['systemRootPath'] . 'objects/configuration.php';
    $config = new AVideoConf();
}

$showOnlyLoggedUserVideos = false;
$title = $config->getWebSiteTitle();
$link = $global['webSiteRootURL'];
$author = $config->getContactEmail();
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
    $author = $user['email'];
    if (!isHTMLEmpty($about)) {
        $description = $about;
    }
    $link = User::getChannelLink($user['id']);
    $logo = User::getPhoto($user['id']);
}

$cacheName = "feedCache" . md5(json_encode($_REQUEST));
$rows = ObjectYPT::getCache($cacheName, 0);
if (empty($rows)) {
    // send $_REQUEST['catName'] to be able to filter by category
    $sort = @$_POST['sort'];
    if (empty($_REQUEST['program_id'])) {
        if (empty($_POST['sort'])) {
            $_POST['sort'] = array('created' => 'DESC');
        }
        $rows = Video::getAllVideos("viewable", $showOnlyLoggedUserVideos);
    } else {
        unset($_POST['sort']);
        $playlists_id = intval($_REQUEST['program_id']);
        $videosArrayId = PlayList::getVideosIdFromPlaylist($playlists_id);
        $rows = Video::getAllVideos("viewable", false, true, $videosArrayId, false, true);
        $rows = PlayList::sortVideos($rows, $videosArrayId);
    }
    $_POST['sort'] = $sort;
    ObjectYPT::setCache($cacheName, $rows);
} else {
    $rows = object_to_array($rows);
}


if (!empty($_REQUEST['program_id'])) {
    $playlists_id = intval($_REQUEST['program_id']);
    $pl = new PlayList($playlists_id);
    $videosArrayId = PlayList::getVideosIdFromPlaylist($playlists_id);
    $title = PlayLists::getNameOrSerieTitle($playlists_id);
    $link = PlayLists::getLink($playlists_id);
    $users_id = $pl->getUsers_id();
    $new_author = User::getEmailDb($users_id);
    if(!empty($new_author)){
        $author = $new_author;
    }
    $description = PlayLists::getDescriptionIfIsSerie($playlists_id);
    //var_dump($videosArrayId);foreach ($rows as $value) {var_dump($value['id']);}exit;
}

if (empty($description)) {
    $description = $title;
}
//var_dump($title, $cacheName, $_REQUEST);exit;
if (!empty($_REQUEST['roku'])) {
    include $global['systemRootPath'] . 'feed/roku.json.php';
} elseif (!empty($_REQUEST['rokuSearch'])) {
    include $global['systemRootPath'] . 'feed/roku.search.json.php';
} elseif (!empty($_REQUEST['vizio'])) {
    include $global['systemRootPath'] . 'feed/vizio.json.php';
} elseif (empty($_REQUEST['mrss'])) {
    include $global['systemRootPath'] . 'feed/rss.php';
} else {
    include $global['systemRootPath'] . 'feed/mrss.php';
}

function feedText($text) {
    return trim(str_replace(['&&'], ['&'], str_replace(['&nbsp;', '&', '<', '>'], [' ', '&amp;', '&lt;', '&gt;'], (strip_tags(br2nl($text))))));
}
