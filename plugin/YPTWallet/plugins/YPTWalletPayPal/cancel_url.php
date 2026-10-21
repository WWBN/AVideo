<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");
$invoice = is_string($_GET['invoice'] ?? null) ? $_GET['invoice'] : '';
$checkout = (!empty($paypal) && PayPalYPT_checkout::isInstalled()) ? PayPalYPT_checkout::getByInvoice($invoice) : false;
if (!empty($checkout)) {
    if ($checkout['status'] === 'created') {
        // Not final: the same approval link may still be completed in another tab, which the status check detects.
        PayPalYPT_checkout::update($checkout['id'], ['status_detail' => 'buyer_canceled']);
    }
    header('Location: ' . PayPalYPT::getCheckoutPageURL($checkout['invoice_number']));
} elseif (!empty($_SESSION['addFunds_Cancel'])) {
    header("Location: {$_SESSION['addFunds_Cancel']}");
} else {
    header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=cancel");
}
