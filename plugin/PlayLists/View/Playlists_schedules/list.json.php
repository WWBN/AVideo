<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/Objects/Playlists_schedules.php';
header('Content-Type: application/json');

if (!User::canStream()) {
    die(json_encode(['error' => true, 'msg' => "You can't do this"]));
}

$plugin = AVideoPlugin::loadPluginIfEnabled('PlayLists');
if (empty($plugin)) {
    die(json_encode(['error' => true, 'msg' => __("The plugin is disabled")]));
}

$program_id = @$_REQUEST['program_id'];
if (!empty($program_id) && !PlayLists::canManagePlaylist($program_id)) {
    die(json_encode(['error' => true, 'msg' => "You can't do this"]));
}

$rows = Playlists_schedules::getAll($program_id);

// non-managers only see schedules for playlists they own/manage, never every private playlist's schedule
if (!PlayLists::canManageAllPlaylists()) {
    foreach ($rows as $key => $value) {
        if (!PlayLists::canManagePlaylist($value['playlists_id'])) {
            unset($rows[$key]);
        }
    }
    $rows = array_values($rows);
}

foreach ($rows as $key => $value) {
    $rows[$key]['loopText'] = empty($rows[$key]['loop'])?__('No'):__('Yes');
    $rows[$key]['statusText'] = empty($rows[$key]['status'])?__('Inactive'):Playlists_schedules::STATUS_TEXT[$rows[$key]['status']];
    $rows[$key]['repeatText'] = __(Playlists_schedules::$REPEAT_TEXT[$rows[$key]['repeat']]);
    $rows[$key]['start_datetime'] = date('Y-m-d H:i', $rows[$key]['start_datetime']);
    $rows[$key]['finish_datetime'] = date('Y-m-d H:i', $rows[$key]['finish_datetime']);
}
?>
{"data": <?php echo json_encode($rows); ?>}
