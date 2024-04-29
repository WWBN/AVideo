<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_video_publisher_logs.php';
header('Content-Type: application/json');

$rows = Publisher_video_publisher_logs::getAll();
$total = Publisher_video_publisher_logs::getTotal();

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>