<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
header('Content-Type: application/json');

if (!User::canStream()) {
    die('{"data": []}');
}

if (empty($_GET['users_id'])) {
    if (!User::isAdmin()) {
        $_GET['users_id'] = User::getId();
    }
}

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