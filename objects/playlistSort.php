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

if(empty($_POST['playlist_id']) && !empty($_GET['playlist_id'])){
    $_POST['playlist_id'] = intval($_GET['playlist_id']);
}

$obj = new PlayList($_POST['playlist_id']);
if(User::getId() != $obj->getUsers_id()){
    die('{"error":"'.__("Permission denied").'"}');
}

$count = 1;

if(empty($_POST['list'])){
    // get all videos from playlist
    $videosArrayId = PlayList::getVideosIdFromPlaylist($_POST['playlist_id']);
    $videos = array();    
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


foreach ($_POST['list'] as $key => $value) {
    $result = $obj->addVideo($value, true, $count++);
}

if(!empty($_GET['sort'])){
    header("Location: ". User::getChannelLink($obj->getUsers_id()));
    exit;
}

echo '{"status":"'.$result.'"}';


// Comparison function
function dateCmp($videoA, $videoB) {
    $a = strtotime($videoA['created']);
    $b = strtotime($videoB['created']);
    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}
function dateCmpDesc($videoA, $videoB) {
    $a = strtotime($videoA['created']);
    $b = strtotime($videoB['created']);
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
function titleASC($videoA, $videoB) {
    return strcasecmp($videoA['title'], $videoB['title']);
}
function titleDESC($videoA, $videoB) {
    return strcasecmp($videoB['title'], $videoA['title']);
}
