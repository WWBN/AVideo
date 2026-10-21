<?php
require_once '../../videos/configuration.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

if (!User::isLogged()) {
    forbiddenPage('Login required');
}
forbidIfNotPost();
forbidIfInvalidToken();

$response = ['error' => true, 'msg' => __('An error occurred')];
$paypal = AVideoPlugin::loadPluginIfEnabled('PayPalYPT');
$invoice = is_string($_POST['invoice'] ?? null) ? $_POST['invoice'] : '';
$checkout = (!empty($paypal) && PayPalYPT_checkout::isInstalled()) ? PayPalYPT_checkout::getByInvoice($invoice) : false;
if (empty($checkout) || intval($checkout['users_id']) !== intval(User::getId())) {
    forbiddenPage('Checkout not found');
}
try {
    if (!in_array($checkout['status'], PayPalYPT_checkout::FINAL_STATES, true)) {
        try {
            // Asks PayPal again: completes an approved payment even if the buyer closed the PayPal tab.
            $checkout = PayPalYPT::reconcileCheckout($checkout, 'status');
        } catch (PayPalCheckoutBusyException $busy) {
            // Another request is completing this checkout right now; the next poll reads its result.
        } catch (\Throwable $th) {
            _error_log("PayPal checkout status {$checkout['invoice_number']}: " . PayPalYPT::describeApiError($th), AVideoLog::$WARNING);
            $response['retry'] = true;
        }
    }
    if ($checkout['status'] === 'completed' || $checkout['status'] === 'trial') {
        // The session may have cached "no access" before the purchase.
        ObjectYPT::deleteAllSessionCache();
    }
    $response = array_merge($response, PayPalYPT::getPublicCheckoutStatus($checkout));
    $response['error'] = false;
    $response['msg'] = '';
} catch (\Throwable $th) {
    _error_log('PayPal checkout status failed: ' . PayPalYPT::describeApiError($th), AVideoLog::$ERROR);
}
echo json_encode($response);
