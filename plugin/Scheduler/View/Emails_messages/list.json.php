<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Emails_messages.php';
header('Content-Type: application/json');

if (!User::isAdmin()) {
    http_response_code(403);
    die(json_encode(['error' => true, 'msg' => 'Not authorized']));
}

$rows = Emails_messages::getAll();
$total = Emails_messages::getTotal();

?>
{"data": <?php echo json_encode($rows); ?>, "draw": <?php echo intval(@$_REQUEST['draw']); ?>, "recordsTotal":<?php echo $total; ?>, "recordsFiltered":<?php echo $total; ?>}
