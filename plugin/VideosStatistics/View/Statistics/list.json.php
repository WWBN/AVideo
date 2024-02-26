<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideosStatistics/Objects/Statistics.php';
header('Content-Type: application/json');

$rows = Statistics::getAll();
$total = Statistics::getTotal();

?>
{"data": <?php echo json_encode($rows); ?>, "draw": <?php echo intval(@$_REQUEST['draw']); ?>, "recordsTotal":<?php echo $total; ?>, "recordsFiltered":<?php echo $total; ?>}