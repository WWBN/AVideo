<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/Objects/Playlists_schedules.php';
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('PlayLists');
if (empty($plugin)) {
    forbiddenPage(__("The plugin is disabled"));
}

if (!User::canStream()) {
    forbiddenPage(__("You cannot livestream"));
}

// Verify ownership of the existing schedule when editing
if (!empty($_POST['id'])) {
    $existing = new Playlists_schedules(intval($_POST['id']));
    if (!PlayLists::canManagePlaylist($existing->getPlaylists_id())) {
        forbiddenPage(__("You cannot modify this schedule"));
    }
}

// Verify ownership of the target playlist
if (!PlayLists::canManagePlaylist($_POST['playlists_id'])) {
    forbiddenPage(__("You cannot manage this playlist"));
}

$o = new Playlists_schedules(@$_POST['id']);
$o->setPlaylists_id($_POST['playlists_id']);
$o->setName($_POST['name']);
$o->setDescription($_POST['description']);
//$o->setStatus($_POST['status']);
$o->setLoop($_POST['loop']);
$o->setStart_datetime($_POST['start_datetime']);
$o->setFinish_datetime($_POST['finish_datetime']);
$o->setRepeat($_POST['repeat']);
$o->setParameters($_POST['parameters']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
