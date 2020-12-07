<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/Objects/Playlists_schedules.php';
header('Content-Type: application/json');

$rows = Playlists_schedules::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}