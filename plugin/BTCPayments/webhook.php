<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
header('Content-Type: application/json');
$global['bypassSameDomainCheck'] = 1;
$obj = AVideoPlugin::getDataObjectIfEnabled('BTCPayments');
if (empty($obj)) {
    forbiddenPage('Plugin is disabled');
}

$log = getVideosDir() . 'btc_webhook.log';

if (empty($_REQUEST['invoiceId'])) {
    forbiddenPage('Empty Invoice ID');
}

$resp = new stdClass();

_error_log('BTC::webhook - Received webhook for invoiceId: ' . $_REQUEST['invoiceId'], AVideoLog::$DEBUG);

$verificationURL = BTCPayments::getMarketplaceURL('invoice.verify.json.php');
$verificationURL = addQueryStringParameter($verificationURL, 'invoiceId', $_REQUEST['invoiceId']);
$verificationURL = addQueryStringParameter($verificationURL, 'BTCMarketPlaceKey', $obj->BTCMarketPlaceKey);

_error_log('BTC::webhook - Calling marketplace verification URL (redacted key)', AVideoLog::$DEBUG);

// The marketplace is a fixed public HTTPS server: verified unless $global['verifySSLCertificates'] = 0.
$resp->content = url_get_contents($verificationURL, '', 0, false, false, false, shouldVerifySSLCertificates(true));
if (empty($resp->content)) {
    _error_log('BTC::webhook - ERROR: Empty response from marketplace verification', AVideoLog::$ERROR);
    forbiddenPage('Empty Content');
}

_error_log('BTC::webhook - Verification response received, length: ' . strlen($resp->content), AVideoLog::$DEBUG);

$resp->json = json_decode($resp->content);
if (!empty($resp->json)) {
    _error_log('BTC::webhook - Invoice verification successful for ID: ' . $_REQUEST['invoiceId'], AVideoLog::$DEBUG);
    unset($resp->content);
} else {
    _error_log('BTC::webhook - ERROR: Failed to decode verification response', AVideoLog::$ERROR);
    _error_log('BTC::webhook - Response content: ' . $resp->content, AVideoLog::$ERROR);
}

file_put_contents($log, '[' . date('Y/m/d H:i:s') . '] ' . json_encode($resp) . PHP_EOL, FILE_APPEND);

$invoice = $resp->json->invoice;

if (empty($invoice)) {
    forbiddenPage('Empty invoice');
}

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
