<?php
require_once '../../videos/configuration.php';
if (!User::isLogged()) {
    forbiddenPage('Login required');
}
// BTCPay's "Return to store" button: open the Bitcoin payment page of that order, or of the latest invoice.
$target = false;
if (!empty(AVideoPlugin::loadPluginIfEnabled('BTCPayments'))) {
    $order = is_string($_GET['order'] ?? null) ? $_GET['order'] : '';
    $invoiceId = is_string($_GET['invoice'] ?? null) ? $_GET['invoice'] : '';
    foreach (Btc_invoices::getRecentFromUser(User::getId(), 20) as $row) {
        $data = BTCPayments::getInvoiceData($row);
        if (($order !== '' && ($data['metadata']['orderId'] ?? '') === $order)
            || ($invoiceId !== '' && $row['invoice_identification'] === $invoiceId)) {
            $target = $row;
            break;
        }
        if (empty($target)) {
            $target = $row;
        }
    }
}
if (empty($target)) {
    header('Location: ' . $global['webSiteRootURL'] . 'plugin/YPTWallet/view/addFunds.php');
    exit;
}
header('Location: ' . BTCPayments::getCheckoutPageURL($target['invoice_identification']));
