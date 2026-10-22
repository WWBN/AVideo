<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
header('Content-Type: application/json');
$global['bypassSameDomainCheck'] = 1;
$obj = AVideoPlugin::getDataObjectIfEnabled('BTCPayments');
if (empty($obj)) {
    forbiddenPage('Plugin is disabled');
}

$log = getVideosDir() . 'btc_webhook.log';

if (empty($_REQUEST['invoiceId']) || !is_string($_REQUEST['invoiceId'])) {
    forbiddenPage('Empty Invoice ID');
}

_error_log('BTC::webhook - Received webhook for invoiceId: ' . $_REQUEST['invoiceId'], AVideoLog::$DEBUG);

$invoice = null;
try {
    // The notification only names the invoice; its contents are read again through the marketplace.
    $invoice = BTCPayments::fetchVerifiedInvoice($_REQUEST['invoiceId']);
} catch (\Throwable $th) {
    _error_log('BTC::webhook - ' . $th->getMessage(), AVideoLog::$ERROR);
}

file_put_contents($log, '[' . date('Y/m/d H:i:s') . '] ' . json_encode(['invoiceId' => $_REQUEST['invoiceId'], 'invoice' => $invoice]) . PHP_EOL, FILE_APPEND);

if (empty($invoice)) {
    forbiddenPage('Empty invoice');
}

$resp = new stdClass();
try {
    $paymentResult = BTCPayments::processVerifiedInvoice($invoice);
    foreach (get_object_vars($paymentResult) as $key => $value) {
        $resp->$key = $value;
    }
} catch (\Throwable $error) {
    _error_log('BTC::webhook - Payment processing failed; retry invoice ' . $invoice->id . ': ' . $error->getMessage(), AVideoLog::$ERROR);
    http_response_code(500);
    $resp->error = true;
    $resp->msg = 'Payment could not be processed';
}

echo json_encode($resp);
