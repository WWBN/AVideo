<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_extra_info.php';
header('Content-Type: application/json');

$rows = Users_extra_info::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}