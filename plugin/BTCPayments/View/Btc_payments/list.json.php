<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_payments.php';
header('Content-Type: application/json');

$rows = Btc_payments::getAll();
$total = Btc_payments::getTotal();

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>