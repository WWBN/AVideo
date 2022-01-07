<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_schedule.php';
header('Content-Type: application/json');

if (!User::canStream()) {
    $rows = [];
    $total = 0;
} else {
    if (empty($_POST['sort'])) {
        $_POST['sort']['scheduled_time'] = 'DESC';
    }
    $rows = Live_schedule::getAll(User::getId(), 24);
    $total = Live_schedule::getTotal();
}
?>
{"data": <?php echo json_encode($rows); ?>, "draw": <?php echo intval(@$_REQUEST['draw']); ?>, "recordsTotal":<?php echo $total; ?>, "recordsFiltered":<?php echo $total; ?>}