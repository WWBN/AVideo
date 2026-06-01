<?php
/**
 * Returns the list of FFMPEG log records for a given restream destination.
 * Only the owner of the restream or an admin may call this endpoint.
 */
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams_logs.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';

header('Content-Type: application/json');

if (!AVideoPlugin::isEnabledByName('Live')) {
    forbiddenPage('Plugin is disabled');
}

if (!Live::canRestream()) {
    forbiddenPage(__("You can not do this"));
}

$live_restreams_id = intval(@$_REQUEST['live_restreams_id']);

if (empty($live_restreams_id)) {
    echo json_encode(['error' => true, 'msg' => __('Missing live_restreams_id')]);
    exit;
}

// Enforce ownership: non-admins may only query their own restreams
if (!User::isAdmin()) {
    $lr = new Live_restreams($live_restreams_id);
    if ($lr->getUsers_id() !== User::getId()) {
        forbiddenPage(__("You have no access to this restream"));
        exit;
    }
    $rows = Live_restreams_logs::getHistoryByRestream($live_restreams_id, User::getId());
} else {
    $rows = Live_restreams_logs::getHistoryByRestream($live_restreams_id, 0);
}

// Return only fields needed by the UI
$result = [];
foreach ($rows as $row) {
    $result[] = [
        'id'                          => intval($row['id']),
        'live_transmitions_history_id' => intval($row['live_transmitions_history_id']),
        'logFile'                     => basename($row['logFile']),
    ];
}

echo json_encode(['error' => false, 'rows' => $result]);
