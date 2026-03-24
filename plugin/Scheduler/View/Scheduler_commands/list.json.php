<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Scheduler_commands.php';
header('Content-Type: application/json');

if (!User::isAdmin()) {
    http_response_code(403);
    die(json_encode(['error' => true, 'msg' => 'Not authorized']));
}

$rows = Scheduler_commands::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}
