<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
if (!User::isLogged()) {
    die();
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
header('Content-Type: application/json');
_session_write_close();
//mysqlBeginTransaction();
//var_dump($row);exit;
setRowCount(10000);
$obj = new stdClass();
$obj->error = false;
$obj->msg = '';
$obj->videosPlaylistsIds = array();
$obj->videosPlaylists = array();
$obj->rows = PlayList::getAllFromUser(User::getId(), false);
foreach ($obj->rows as $key => $value) {
    foreach ($obj->rows[$key]['videos'] as $key2 => $value2) {
        unset($obj->rows[$key]['videos'][$key2]['description']);
        if(!empty($obj->rows[$key]['videos'][$key2]['id'])){
            $videos_id = $obj->rows[$key]['videos'][$key2]['id'];
            if(!isset($obj->videosPlaylistsIds[$videos_id])){
                $obj->videosPlaylistsIds[$videos_id] = array();
            }
            if(!in_array($obj->rows[$key]['id'], $obj->videosPlaylistsIds[$videos_id])){
                $obj->videosPlaylistsIds[$videos_id][] = $obj->rows[$key]['id'];
                $pl = $obj->rows[$key];
                unset($pl['videos']);
                $obj->videosPlaylists[$videos_id][] = $pl;
            }
        }
    }
}

$obj->playListGetAllFromUserWasCache = !empty($playListGetAllFromUserWasCache);
$obj->getVideosFromPlaylistWasCache = !empty($getVideosFromPlaylistWasCache);

//mysqlCommit();
echo json_encode($obj);
