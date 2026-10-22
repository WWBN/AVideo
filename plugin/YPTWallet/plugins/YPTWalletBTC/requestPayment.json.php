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
forbidIfIsUntrustedRequest('YPTWalletBTC::requestPayment');
forbidIfNotPost();
forbidIfInvalidToken();

$obj = new stdClass();
$obj->error = true;
$obj->msg = __('An error occurred');
if (empty(AVideoPlugin::loadPluginIfEnabled('BTCPayments')) || empty(AVideoPlugin::loadPluginIfEnabled('YPTWallet'))) {
    // BTCPayments (and its exception class) is not loaded when the plugin is disabled.
    $obj->msg = __('Bitcoin payments are not available right now. You were not charged. Please try again.');
    die(json_encode($obj));
}
try {
    // Price, product and buyer are resolved on the server; the browser only names the selection.
    $result = BTCPayments::startCheckout(User::getId(), [
        'plugin' => is_string($_POST['plugin'] ?? null) ? $_POST['plugin'] : '',
        'value' => is_string($_POST['value'] ?? null) ? $_POST['value'] : '',
        'plans_id' => intval($_POST['plans_id'] ?? 0),
        'videos_id' => intval($_POST['videos_id'] ?? 0),
        'ppvlive_schedule_id' => intval($_POST['ppvlive_schedule_id'] ?? 0),
    ]);
    $obj->error = false;
    $obj->msg = $result['message'] ?? '';
    $obj->invoice = $result['invoice'];
    $obj->statusURL = BTCPayments::getCheckoutPageURL($result['invoice']);
} catch (BTCPaymentsCheckoutRefused $refused) {
    $obj->msg = $refused->getMessage();
} catch (\Throwable $th) {
    _error_log('YPTWalletBTC::requestPayment failed: ' . get_class($th) . ' ' . $th->getMessage(), AVideoLog::$ERROR);
}
die(json_encode($obj));
