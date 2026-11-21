<?php

require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('Live');

if (!User::canStream()) {
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Live_restreams($id);
if (!User::isAdmin() && $row->getUsers_id()!= User::getId()) {
    $obj->msg = "You are not admin";
    die(json_encode($obj));
}
if (empty($row->getId())) {
    $obj->msg = "Destination not found";
    die(json_encode($obj));
}
// Deleting only the row used to leave its FFmpeg relay sending the live to the old channel.
// Stop it first; the restreamer stops nothing when the destination is not running.
$relays = Live_restreams::stopRelaysOfDestination($id);
$obj->stopped = $relays['stopped'];
if ($relays['failed'] > 0) {
    $obj->msg = __('This destination is still sending a live and it could not be stopped, so it was not deleted. Stop it in Active Lives and try again.');
    die(json_encode($obj));
}
$deletedChannel = LiveRestreamDestinationInfo::getChannelFromParameters($row->getParameters());
_error_log('Live_restreams/delete.json.php: deleting live_restreams_id=' . intval($id) . ' by users_id=' . User::getId()
    . ' name=' . json_encode((string) $row->getName()) . ' channel=' . json_encode(LiveRestreamDestinationInfo::getChannelLabel($deletedChannel))
    . ' relays_stopped=' . intval($relays['stopped']));
$obj->error = !$row->delete();
if (!$obj->error) {
    Live_restreams::deleteDestinationStatus($id);
    if ($obj->stopped > 0) {
        $obj->msg = __('The live that was still being sent to this destination was stopped.');
    }
}
die(json_encode($obj));
