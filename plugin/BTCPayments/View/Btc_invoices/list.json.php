<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_invoices.php';
header('Content-Type: application/json');

if (!User::isLogged()) {
    forbiddenPage(__("You cannot do this"));
    exit;
}

$rows = Btc_invoices::getAllFromUser(User::getId()) ?: [];
$total = Btc_invoices::getTotalFromUser(User::getId());
$btcPlugin = AVideoPlugin::loadPluginIfEnabled('BTCPayments');
$reconciled = 0;

foreach ($rows as $key => $value) {
    if (!empty($btcPlugin)) {
        $public = BTCPayments::getPublicInvoiceStatus($value);
        // A recent unfinished invoice may have been paid or expired without a notification: ask BTCPay (a few per page).
        if (!$public['final'] && intval($value['created_php_time']) > time() - 2 * 86400 && $reconciled < 3) {
            $reconciled++;
            try {
                $value = BTCPayments::reconcileInvoice($value);
                $public = BTCPayments::getPublicInvoiceStatus($value);
            } catch (\Throwable $th) {
                _error_log('BTC invoice list: could not check invoice ' . $value['invoice_identification'] . ': ' . $th->getMessage(), AVideoLog::$WARNING);
            }
        }
        $public['checkout_page'] = BTCPayments::getCheckoutPageURL($value['invoice_identification']);
        $rows[$key] = $value;
        $rows[$key]['public'] = $public;
    }
    $rows[$key]['json_object'] = json_decode($rows[$key]['json']);
}

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>
