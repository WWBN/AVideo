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
} elseif (empty($_GET['users_id'])) {
    // SECURITY: previously fell through to Live_restreams::getAll() here, returning
    // every account's restream destinations (incl. stream keys) to any isAdmin=1 user.
    // On a multi-tenant install (many unrelated clients, some flagged isAdmin so they
    // can self-manage their own account) that leaked other customers' destination
    // credentials. Default an admin's own listing to their own destinations too - an
    // admin who genuinely needs another specific account's destinations can still pass
    // users_id explicitly.
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
