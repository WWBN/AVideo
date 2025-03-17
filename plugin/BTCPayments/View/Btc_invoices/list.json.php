<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_invoices.php';
header('Content-Type: application/json');

if (!User::isLogged()) {
    forbiddenPage(__("You cannot do this"));
    exit;
}

$rows = Btc_invoices::getAllFromUser(User::getId());
$total = Btc_invoices::getTotalFromUser(User::getId());

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
