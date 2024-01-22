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
        continue;
    }
    $processed[] = $value['id'];
    $ps = Playlists_schedules::getPlaying($value['id']);
    if ($value['finish_datetime'] < time()) {
        PlayLists::setScheduleStatus($key, Playlists_schedules::STATUS_COMPLETE);
        continue;
    }
    $pl = new PlayList($ps->playlists_id);
    $title = $pl->getName() . ' [' . $ps->msg . ']';
    $title = '';
    $response = Rebroadcaster::rebroadcastVideo($ps->current_videos_id, $pl->getUsers_id(), Playlists_schedules::getPlayListScheduledIndex($value['id']), $title);
    //var_dump($response, $ps);
}

$rows = Playlists_schedules::getAllActive();
foreach ($rows as $key => $value) {
    if(in_array($value['id'], $processed)){
        continue;
    }
    $processed[] = $value['id'];
    if ($value['start_datetime'] > time()) {
        continue;
    }
    $ps = Playlists_schedules::getPlaying($value['id']);
    $pl = new PlayList($ps->playlists_id);
    $title = $pl->getName() . ' [' . $ps->msg . ']';
    $title = '';
    $response = Rebroadcaster::rebroadcastVideo($ps->current_videos_id, $pl->getUsers_id(), Playlists_schedules::getPlayListScheduledIndex($value['id']), $title);
    //var_dump($response, $ps);
}
