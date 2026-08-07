<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
header('Content-Type: application/json');

if (!User::canStream()) {
    die('{"data": []}');
}

if (!User::isAdmin()) {
    // Non-admin users are always restricted to their own records.
    // The empty() guard was bypassable by supplying users_id explicitly (IDOR).
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
