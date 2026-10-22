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
$plugin = AVideoPlugin::loadPluginIfEnabled('BTCPayments');
$invoiceId = is_string($_POST['invoice'] ?? null) ? $_POST['invoice'] : '';
$row = (!empty($plugin) && $invoiceId !== '') ? Btc_invoices::getFromInvoice_identification($invoiceId) : false;
if (empty($row) || intval($row['users_id']) !== intval(User::getId())) {
    forbiddenPage('Checkout not found');
}
try {
    $status = BTCPayments::getPublicInvoiceStatus($row);
    if (!$status['final']) {
        try {
            // Asks BTCPay again: credits a settled payment even if its notification never reached this site.
            $row = BTCPayments::reconcileInvoice($row);
        } catch (\Throwable $th) {
            _error_log("BTC checkout status {$invoiceId}: " . $th->getMessage(), AVideoLog::$WARNING);
            $response['retry'] = true;
        }
        $status = BTCPayments::getPublicInvoiceStatus($row);
    }
    if ($status['credited']) {
        // The session may have cached "no access" or the old balance before the purchase.
        ObjectYPT::deleteAllSessionCache();
    }
    $response = array_merge($response, $status);
    $response['error'] = false;
    $response['msg'] = '';
} catch (\Throwable $th) {
    _error_log('BTC checkout status failed: ' . $th->getMessage(), AVideoLog::$ERROR);
}
echo json_encode($response);
