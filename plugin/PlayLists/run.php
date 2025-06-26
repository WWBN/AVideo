<?php
require_once __DIR__ . '/../../videos/configuration.php';

if (!isCommandLineInterface()) {
    if (!User::isAdmin()) {
        forbiddenPage();
    }

    if (!AVideoPlugin::loadPlugin("Rebroadcaster")) {
        forbiddenPage(__("Rebroadcaster plugin is required"));
    }

    $obj = AVideoPlugin::getObjectDataIfEnabled("PlayLists");
    if (empty($obj)) {
        forbiddenPage(__("PlayLists is disabled"));
    }
} else {
    if (!AVideoPlugin::loadPlugin("Rebroadcaster")) {
        return false;
    }

    $obj = AVideoPlugin::getObjectDataIfEnabled("PlayLists");
    if (empty($obj)) {
        return false;
    }
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
    if (in_array($value['id'], $processed)) {
        _error_log("Playlist rebroadcast line " . __LINE__);
        continue;
    }
    $processed[] = $value['id'];
    $ps = Playlists_schedules::getPlaying($value['id']);
    if ($value['finish_datetime'] < time()) {
        _error_log("Playlist rebroadcast line " . __LINE__);
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
    if (in_array($value['id'], $processed)) {
        _error_log("Playlist rebroadcast line " . __LINE__);
        continue;
    }
    $processed[] = $value['id'];
    if ($value['start_datetime'] > time()) {
        _error_log("Playlist rebroadcast line " . __LINE__);
        continue;
    }
    $ps = Playlists_schedules::getPlaying($value['id']);
    $pl = new PlayList($ps->playlists_id);
    $title = $pl->getName() . ' [' . $ps->msg . ']';
    $title = '';
    _error_log("Playlist rebroadcast active id={$value['id']} videos_id={$ps->current_videos_id} [total=" . count($rows) . "]");
    $response = Rebroadcaster::rebroadcastVideo($ps->current_videos_id, $pl->getUsers_id(), Playlists_schedules::getPlayListScheduledIndex($value['id']), $title);
    //var_dump($response, $ps);
}

$rows = Playlists_schedules::getAllExecuting();
foreach ($rows as $key => $value) {
    if (in_array($value['id'], $processed)) {
        _error_log("Playlist rebroadcast line " . __LINE__);
        continue;
    }
    $processed[] = $value['id'];
    if ($value['finish_datetime'] < time()) {
        _error_log("Playlist rebroadcast line " . __LINE__);
        PlayLists::setScheduleStatus($key, Playlists_schedules::STATUS_COMPLETE);
        continue;
    }
    $forceIndex = Playlists_schedules::getPlayListScheduledIndex($value['id']);

    $stats = Live::getStatsApplications(true);
    $found = false; // Flag to indicate if the desired condition is met

    foreach ($stats as $key => $apps) {
        if (preg_match("/.*{$forceIndex}$/", $apps['key'])) {
            $found = true;
            break; // Breaks the inner loop
        }
    }

    //var_dump($value['key'], $found);
    if (!$found) {
        $ps = Playlists_schedules::getPlaying($value['id']);
        $pl = new PlayList($ps->playlists_id);
        $title = $pl->getName() . ' [' . $ps->msg . ']';
        $title = '';
        _error_log("Playlist rebroadcast executing but not found {$value['id']}");
        $response = Rebroadcaster::rebroadcastVideo($ps->current_videos_id, $pl->getUsers_id(), $forceIndex, $title);
    }
    //var_dump($response, $ps);
}
