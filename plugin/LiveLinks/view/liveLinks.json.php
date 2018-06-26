<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';
header('Content-Type: application/json');

$rows = LiveLinksTable::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}