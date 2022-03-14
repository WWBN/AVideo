<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_servers.php';
header('Content-Type: application/json');

$rows = Live_servers::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}