<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_schedule_has_users_groups.php';
header('Content-Type: application/json');

$rows = Meet_schedule_has_users_groups::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}