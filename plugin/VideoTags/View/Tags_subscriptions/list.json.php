<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/Tags_subscriptions.php';
header('Content-Type: application/json');

if (!User::isAdmin()) {
    die(json_encode(['error' => true, 'msg' => "You can't do this"]));
}

$rows = Tags_subscriptions::getAll();
$total = Tags_subscriptions::getTotal();

?>
{"data": <?php echo json_encode($rows); ?>, "draw": <?php echo intval(@$_REQUEST['draw']); ?>, "recordsTotal":<?php echo $total; ?>, "recordsFiltered":<?php echo $total; ?>}
