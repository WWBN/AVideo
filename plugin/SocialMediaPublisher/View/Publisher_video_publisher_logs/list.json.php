<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/publisher_video_publisher_logs.php';
header('Content-Type: application/json');

$rows = publisher_video_publisher_logs::getAll();
$total = publisher_video_publisher_logs::getTotal();

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>