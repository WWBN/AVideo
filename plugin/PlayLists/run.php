<?php
require_once __DIR__.'/../../videos/configuration.php';

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

$rows = Playlists_schedules::getAllExecuting();
foreach ($rows as $key => $value) {
    $ps = Playlists_schedules::getPlaying($value['id']);
    $response = Rebroadcaster::rebroadcastVideo($ps->current_videos_id, 0, "ps-{$value['id']}");
    var_dump($response, $ps);
}

$rows = Playlists_schedules::getAllActive();
foreach ($rows as $key => $value) {
    $ps = Playlists_schedules::getPlaying($value['id']);
    $response = Rebroadcaster::rebroadcastVideo($ps->current_videos_id, 0, "ps-{$value['id']}");
    var_dump($response, $ps);
}
