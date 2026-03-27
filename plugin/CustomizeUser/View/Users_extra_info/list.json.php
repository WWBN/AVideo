<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_extra_info.php';
header('Content-Type: application/json');

if (!User::isAdmin()) {
    die(json_encode(['error' => true, 'msg' => "You can't do this"]));
}

$rows = Users_extra_info::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}