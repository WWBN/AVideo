<?php
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
allowOrigin();
require_once $global['systemRootPath'] . 'objects/user.php';
require_once 'comment.php';
require_once 'subscribe.php';
// gettig the mobile submited value
$inputJSON = url_get_contents('php://input');
$input = _json_decode($inputJSON, true); //convert JSON into array
if (!empty($input) && empty($_REQUEST)) {
    foreach ($input as $key => $value) {
        $_REQUEST[$key]=$value;
    }
}
if (!empty($_REQUEST['user']) && !empty($_REQUEST['pass'])) {
    $user = new User(0, $_REQUEST['user'], $_REQUEST['pass']);
    $user->login(false, true);
}
if (empty($_REQUEST['playlists_id'])) {
    die('Play List can not be empty');
}
require_once './playlist.php';
$videos = PlayList::getVideosFromPlaylist($_REQUEST['playlists_id']);
$objMob = AVideoPlugin::getObjectData("MobileManager");
$index = 0;
foreach ($videos as $key => $value) {
    $videos[$key] = cleanUpRowFromDatabase($videos[$key]);
    $images = Video::getImageFromFilename($videos[$key]['filename'], $videos[$key]['type']);
    $videos[$key]['images'] = $images;
    $videos[$key]['Poster'] = !empty($objMob->portraitImage) ? $images->posterPortrait : $images->poster;
    $videos[$key]['Thumbnail'] = !empty($objMob->portraitImage) ? $images->posterPortraitThumbs : $images->thumbsJpg;
    $videos[$key]['imageClass'] = !empty($objMob->portraitImage) ? "portrait" : "landscape";
    $videos[$key]['VideoUrl'] = getVideosURL($videos[$key]['filename']);
    $videos[$key]['createdHumanTiming'] = humanTiming(strtotime($videos[$key]['created']));
    $videos[$key]['pageUrl'] =  PlayLists::getLink($_REQUEST['playlists_id'], false, $index);
    $videos[$key]['embedUrl'] = PlayLists::getLink($_REQUEST['playlists_id'], true, $index);
    unset($_REQUEST['sort'], $_REQUEST['current'], $_REQUEST['searchPhrase']);
    $_REQUEST['rowCount'] = 10;
    $_REQUEST['sort']['created'] = "desc";
    $videos[$key]['comments'] = Comment::getAllComments($videos[$key]['id']);
    $videos[$key]['commentsTotal'] = Comment::getTotalComments($videos[$key]['id']);
    $videos[$key]['comments'] = Comment::addExtraInfo($videos[$key]['comments']);
    $videos[$key]['subscribers'] = Subscribe::getTotalSubscribes($videos[$key]['users_id']);

    $videos[$key]['firstVideo'] = '';
    foreach ($videos[$key]['VideoUrl'] as $value2) {
        if ($value2["type"] === 'video') {
            $videos[$key]['firstVideo'] = $value2["url"];
            break;
        }
    }
    if (preg_match("/^videos/", $videos[$key]['photoURL'])) {
        $videos[$key]['UserPhoto'] = "{$global['webSiteRootURL']}".$videos[$key]['photoURL'];
    } else {
        $videos[$key]['UserPhoto'] = $videos[$key]['photoURL'];
    }
    $index++;
}

echo json_encode($videos);
