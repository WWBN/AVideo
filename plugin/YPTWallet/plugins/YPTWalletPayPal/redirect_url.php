<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

// The buyer comes back from PayPal. The saved checkout identifies the buyer, the product and the price,
// so this works without the original session; the result page shows the status to the buyer.
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");
$invoice = is_string($_GET['invoice'] ?? null) ? $_GET['invoice'] : '';
$checkout = (!empty($paypal) && PayPalYPT_checkout::isInstalled()) ? PayPalYPT_checkout::getByInvoice($invoice) : false;

if (empty($checkout)) {
    // An approval started before this update, or an unknown link: nothing was executed, so nothing was charged.
    _error_log('PayPal return without a saved checkout; payment not executed', AVideoLog::$WARNING);
    header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");
    exit;
}

// PayPal's own query parameters must name the same payment; the saved IDs are used either way.
$returnedId = $checkout['purpose'] === 'subscription' ? ($_GET['token'] ?? '') : ($_GET['paymentId'] ?? '');
if (!empty($returnedId) && $returnedId !== $checkout['payment_id']) {
    _error_log("PayPal return: payment ID does not match checkout {$checkout['invoice_number']}", AVideoLog::$SECURITY);
} else {
    try {
        $checkout = PayPalYPT::reconcileCheckout($checkout, 'return');
    } catch (\Throwable $th) {
        // The status page and the webhook/IPN retry the same checkout; the buyer must not pay again.
        _error_log("PayPal return: checkout {$checkout['invoice_number']} not completed yet " . PayPalYPT::describeApiError($th), AVideoLog::$ERROR);
    }
}
header('Location: ' . PayPalYPT::getCheckoutPageURL($checkout['invoice_number']));
exit;
