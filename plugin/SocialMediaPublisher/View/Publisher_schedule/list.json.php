<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_schedule.php';
header('Content-Type: application/json');

if (!User::isAdmin()) {
    die(json_encode(['error' => true, 'msg' => "You can't do this"]));
}

$rows = Publisher_schedule::getAll();
$total = Publisher_schedule::getTotal();

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>