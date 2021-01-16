<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Permissions/Objects/Users_groups_permissions.php';
header('Content-Type: application/json');

$rows = Users_groups_permissions::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}