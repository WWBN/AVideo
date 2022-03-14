<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/PayPalYPT/Objects/PayPalYPT_log.php';
header('Content-Type: application/json');

$rows = PayPalYPT_log::getAll();
$total = PayPalYPT_log::getTotal();

?>
{"data": <?php echo json_encode($rows); ?>, "draw": <?php echo intval(@$_REQUEST['draw']); ?>, "recordsTotal":<?php echo $total; ?>, "recordsFiltered":<?php echo $total; ?>}