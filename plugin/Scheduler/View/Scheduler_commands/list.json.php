<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Scheduler_commands.php';
header('Content-Type: application/json');

$rows = Scheduler_commands::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}