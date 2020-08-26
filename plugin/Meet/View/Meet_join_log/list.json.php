<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_join_log.php';
header('Content-Type: application/json');

$rows = Meet_join_log::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}