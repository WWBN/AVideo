<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
if (!User::isLogged()) {
    die('{"error":"'.__("Permission denied").'"}');
}

if (!PlayLists::canManagePlaylist($_REQUEST['playlist_id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}

$obj = new PlayList($_REQUEST['playlist_id']);
$count = 1;

if (empty($_POST['list'])) {
    // get all videos from playlist
    $videosArrayId = PlayList::getVideosIdFromPlaylist($_REQUEST['playlist_id']);
    $videos = [];
    foreach ($videosArrayId as $value) {
        $videos[] = Video::getVideoLight($value);
    }
    $sortFunc = "titleASC";
    switch ($_GET['sort']) {
        case 1:
            $sortFunc = "titleASC";
            break;
        case 2:
            $sortFunc = "titleDESC";
            break;
        case 3:
            $sortFunc = "dateCmp";
            break;
        case 4:
            $sortFunc = "dateCmpDesc";
            break;
    }
    //var_dump($sortFunc);exit;
    // sort video
    uasort($videos, $sortFunc);

    // transfer the id to the list
    foreach ($videos as $key => $value) {
        $_POST['list'][] = $value['id'];
    }
}
$o = new stdClass();
$o->savedPLItem = array();
$o->playlist_id = $_REQUEST['playlist_id'];

_error_log('playlistSort line='.__LINE__);
mysqlBeginTransaction();
foreach ($_POST['list'] as $key => $value) {
    if(empty($value)){
        continue;
    }
    $order = ($count++);
    $result = $obj->addVideo($value, true, $order, false);
    $o->savedPLItem[] = array(
        'resp'=>$result,
        'videos_id'=>$value,
        'order'=>$order,
    );
}
PlayList::deleteCacheDir($obj->getId());
mysqlCommit();
_error_log('playlistSort line='.__LINE__);

if (!empty($_GET['sort'])) {
    header("Location: ". $_SERVER['HTTP_REFERER']);
    //header("Location: ". User::getChannelLink($obj->getUsers_id()));
    exit;
}
$o->status = $result;
//$o->channelName = $obj->get;
echo json_encode($o);exit;

// Comparison function
function dateCmp($videoA, $videoB)
{
    $a = strtotime($videoA['created']);
    $b = strtotime($videoB['created']);
    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}
function dateCmpDesc($videoA, $videoB)
{
    $a = strtotime($videoA['created']);
    $b = strtotime($videoB['created']);
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
function titleASC($videoA, $videoB)
{
    return strcasecmp($videoA['title'], $videoB['title']);
}
function titleDESC($videoA, $videoB)
{
    return strcasecmp($videoB['title'], $videoA['title']);
}
