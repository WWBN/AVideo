<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/UserNotifications/Objects/User_notifications.php';
header('Content-Type: application/json');
$rows = User_notifications::getAll();
$total = User_notifications::getTotal();

?>
{"data": <?php echo json_encode($rows); ?>, "draw": <?php echo intval(@$_REQUEST['draw']); ?>, "recordsTotal":<?php echo $total; ?>, "recordsFiltered":<?php echo $total; ?>}