<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_schedule.php';
header('Content-Type: application/json');

if(!User::canStream()){
    $rows = array();
    $total = 0;
}else{
    $rows = Live_schedule::getAll(User::getId());
    $total = Live_schedule::getTotal();
}
?>
{"data": <?php echo json_encode($rows); ?>, "draw": <?php echo intval(@$_REQUEST['draw']); ?>, "recordsTotal":<?php echo $total; ?>, "recordsFiltered":<?php echo $total; ?>}