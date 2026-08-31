<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
header('Content-Type: application/json');

if (!User::canStream()) {
    die('{"data": []}');
}

if (empty($_GET['users_id']) || !User::isAdmin()) {
    // Non-admins are always restricted to their own records (empty() alone was bypassable
    // via an explicit users_id - IDOR). Admins default to their own destinations too instead
    // of falling through to Live_restreams::getAll() below, which leaked every account's
    // restream destinations/stream keys on a multi-tenant install - pass users_id explicitly
    // to look up a specific other account.
    $_GET['users_id'] = User::getId();
}

// Read-only after this point, avoid holding the shared session lock during the DB query.
_session_write_close();

if (empty($_GET['users_id'])) {
    $rows = Live_restreams::getAll();
} else {
    $rows = Live_restreams::getAllFromUser($_GET['users_id'], "");
}

foreach ($rows as $key => $value) {
    $rows[$key]['stream_key_short'] = getSEOTitle($value['stream_key'],20);
}
?>
{"data": <?php echo json_encode($rows); ?>}
