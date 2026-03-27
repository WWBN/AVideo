<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Categories_has_users_groups.php';
header('Content-Type: application/json');

if (!User::isAdmin()) {
    die(json_encode(['error' => true, 'msg' => "You can't do this"]));
}

$rows = Categories_has_users_groups::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}