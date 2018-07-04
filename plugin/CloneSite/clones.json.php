<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/Objects/Clones.php';
header('Content-Type: application/json');

$rows = Clones::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}