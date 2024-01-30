<?php
require_once __DIR__ . '/../../videos/configuration.php';

if (!isCommandLineInterface()) {
    forbiddenPage();
}

if (!AVideoPlugin::isEnabledByName("Rebroadcaster")) {
    forbiddenPage(__("Rebroadcaster plugin is required"));
}

$obj = AVideoPlugin::getObjectDataIfEnabled("PlayLists");
if (empty($obj)) {
    forbiddenPage(__("PlayLists is disabled"));
}

require_once $global['systemRootPath'] . 'plugin/PlayLists/Objects/Playlists_schedules.php';
_session_write_close();
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$processed = array();

$rows = Playlists_schedules::getAllExecuted();
foreach ($rows as $key => $value) {
    if(in_array($value['id'], $processed)){
        _error_log("Playlist rebroadcast line ".__LINE__);
        continue;
    }
    $processed[] = $value['id'];
    $ps = Playlists_schedules::getPlaying($value['id']);
    if ($value['finish_datetime'] < time()) {
        _error_log("Playlist rebroadcast line ".__LINE__);
        PlayLists::setScheduleStatus($key, Playlists_schedules::STATUS_COMPLETE);
        continue;
    }
    $pl = new PlayList($ps->playlists_id);
    $title = $pl->getName() . ' [' . $ps->msg . ']';
    $title = '';
    
    _error_log("Playlist rebroadcast executed {$value['id']}");
    $response = Rebroadcaster::rebroadcastVideo($ps->current_videos_id, $pl->getUsers_id(), Playlists_schedules::getPlayListScheduledIndex($value['id']), $title);
    //var_dump($response, $ps);
}

$rows = Playlists_schedules::getAllActive();
foreach ($rows as $key => $value) {
    if(in_array($value['id'], $processed)){
        _error_log("Playlist rebroadcast line ".__LINE__);
        continue;
    }
    $processed[] = $value['id'];
    if ($value['start_datetime'] > time()) {
        _error_log("Playlist rebroadcast line ".__LINE__);
        continue;
    }
    $ps = Playlists_schedules::getPlaying($value['id']);
    $pl = new PlayList($ps->playlists_id);
    $title = $pl->getName() . ' [' . $ps->msg . ']';
    $title = '';
    _error_log("Playlist rebroadcast active id={$value['id']} videos_id={$ps->current_videos_id} [total=".count($rows)."]");
    $response = Rebroadcaster::rebroadcastVideo($ps->current_videos_id, $pl->getUsers_id(), Playlists_schedules::getPlayListScheduledIndex($value['id']), $title);
    //var_dump($response, $ps);
}

$rows = Playlists_schedules::getAllExecuting();
foreach ($rows as $value) {
    if(in_array($value['id'], $processed)){
        _error_log("Playlist rebroadcast line ".__LINE__);
        continue;
    }
    $processed[] = $value['id'];
    $ps = Playlists_schedules::getPlaying($value['id']);
    if ($value['finish_datetime'] < time()) {
        _error_log("Playlist rebroadcast line ".__LINE__);
        PlayLists::setScheduleStatus($key, Playlists_schedules::STATUS_COMPLETE);
        continue;
    }
    $forceIndex = Playlists_schedules::getPlayListScheduledIndex($value['id']);
    $key = Rebroadcaster::getRebroadcastkey($ps->current_videos_id, $forceIndex);

    $stats = getStatsNotifications();
    $found = false;
    foreach ($stats["applications"] as $value) {
       if($value['key'] === $key){
            $found = true;
            break;
       }
    }
    if(!$found){
        $pl = new PlayList($ps->playlists_id);
        $title = $pl->getName() . ' [' . $ps->msg . ']';
        $title = '';
        _error_log("Playlist rebroadcast executed {$value['id']}");
        //$response = Rebroadcaster::rebroadcastVideo($ps->current_videos_id, $pl->getUsers_id(), $forceIndex, $title);
    }
    //var_dump($response, $ps);
}