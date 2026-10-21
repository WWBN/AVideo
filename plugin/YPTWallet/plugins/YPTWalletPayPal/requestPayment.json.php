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
forbidIfIsUntrustedRequest('YPTWalletPayPal::requestPayment');
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
    // Price, product and buyer are resolved on the server; the browser only names the selection.
    $result = PayPalYPT::startOneTimeCheckout(User::getId(), [
        'plugin' => is_string($_POST['plugin'] ?? null) ? $_POST['plugin'] : '',
        'value' => is_string($_POST['value'] ?? null) ? $_POST['value'] : '',
        'plans_id' => intval($_POST['plans_id'] ?? 0),
        'videos_id' => intval($_POST['videos_id'] ?? 0),
        'ppvlive_schedule_id' => intval($_POST['ppvlive_schedule_id'] ?? 0),
    ]);
    $obj->error = false;
    $obj->msg = $result['message'] ?? '';
    $obj->invoice = $result['invoice'] ?? '';
    $obj->approvalLink = $result['approval_url'] ?? '';
    $obj->statusURL = empty($obj->invoice) ? '' : PayPalYPT::getCheckoutPageURL($obj->invoice);
} catch (PayPalCheckoutRefused $refused) {
    $obj->msg = $refused->getMessage();
} catch (\Throwable $th) {
    _error_log('YPTWalletPayPal::requestPayment failed: ' . (class_exists('PayPalYPT') ? PayPalYPT::describeApiError($th) : get_class($th)) . ' ' . $th->getMessage(), AVideoLog::$ERROR);
}
die(json_encode($obj));
