<?php

header('Content-Type: application/json');
header('Cache-Control: no-store');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

if (!User::isLogged()) {
    forbiddenPage('Login required');
}
forbidIfIsUntrustedRequest('YPTWalletPayPal::requestSubscription');
forbidIfNotPost();
forbidIfInvalidToken();

$obj = new stdClass();
$obj->error = true;
$obj->msg = __('An error occurred');
try {
    $plugin = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");
    if (empty($plugin) || empty(AVideoPlugin::loadPluginIfEnabled("YPTWallet"))) {
        throw new PayPalCheckoutRefused(__('PayPal payments are not available.'));
    }
    $plansId = intval($_POST['plans_id'] ?? 0);
    // setUpSubscription() reads the plan from the request.
    $_POST['plans_id'] = $plansId;
    $result = $plugin->startSubscriptionCheckout(User::getId(), $plansId);
    if (!empty($result['approval_url'])) {
        // Same pending row as before; access starts only after PayPal confirms the agreement and its payment.
        Subscription::getOrCreateSubscription(User::getId(), $plansId, '');
    }
    $obj->error = false;
    $obj->msg = $result['message'] ?? '';
    $obj->invoice = $result['invoice'] ?? '';
    $obj->approvalLink = $result['approval_url'] ?? '';
    $obj->statusURL = empty($obj->invoice) ? '' : PayPalYPT::getCheckoutPageURL($obj->invoice);
} catch (PayPalCheckoutRefused $refused) {
    $obj->msg = $refused->getMessage();
} catch (\Throwable $th) {
    _error_log('YPTWalletPayPal::requestSubscription failed: ' . (class_exists('PayPalYPT') ? PayPalYPT::describeApiError($th) : get_class($th)), AVideoLog::$ERROR);
}
die(json_encode($obj));
