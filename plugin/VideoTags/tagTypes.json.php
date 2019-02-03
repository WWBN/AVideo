<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/TagsTypes.php';
header('Content-Type: application/json');

$rows = TagsTypes::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}