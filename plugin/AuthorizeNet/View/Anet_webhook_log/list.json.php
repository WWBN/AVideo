<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AuthorizeNet/Objects/Anet_webhook_log.php';
header('Content-Type: application/json');

$rows = Anet_webhook_log::getAll();
$total = Anet_webhook_log::getTotal();

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>