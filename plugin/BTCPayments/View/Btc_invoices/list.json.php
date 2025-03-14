<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_invoices.php';
header('Content-Type: application/json');

$rows = Btc_invoices::getAll();
$total = Btc_invoices::getTotal();

foreach ($rows as $key => $value) {
    $rows[$key]['json_object'] = json_decode($value['json']);
}

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>
