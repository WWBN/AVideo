<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isLogged()) {
    forbiddenPage("You can not do this");
    exit;
}

$global['bypassSameDomainCheck'] = 1;
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWalletBTC");
$obj = AVideoPlugin::getObjectData("YPTWalletBTC");

if(empty($_REQUEST['orderId'])){
    $_REQUEST['orderId'] = intval(User::getId()).'-'.date('YmdHis');
}

_error_log('BTC::invoice.php - Payment request initiated', AVideoLog::$DEBUG);
_error_log('BTC::invoice.php - Value: ' . $_GET['value'] . ', User: ' . User::getId() . ', OrderId: ' . $_REQUEST['orderId'], AVideoLog::$DEBUG);
_error_log('BTC::invoice.php - Description: ' . $_REQUEST['description'] . ', RedirectUrl: ' . $_REQUEST['redirectUrl'], AVideoLog::$DEBUG);

$invoice = BTCPayments::setUpPayment($_GET['value'], User::getId(), array('description'=>$_REQUEST['description'], 'orderId' => $_REQUEST['orderId']), "{$global['webSiteRootURL']}{$_REQUEST['redirectUrl']}");

if (empty($invoice)) {
    _error_log('BTC::invoice.php - ERROR: setUpPayment returned empty', AVideoLog::$ERROR);
    forbiddenPage("Failed to setup payment");
    exit;
}

if (!empty($invoice['error'])) {
    _error_log('BTC::invoice.php - ERROR: Invoice has error flag', AVideoLog::$ERROR);
    _error_log('BTC::invoice.php - Error response: ' . json_encode($invoice), AVideoLog::$ERROR);
    forbiddenPage("Error creating invoice: " . (!empty($invoice['msg']) ? $invoice['msg'] : 'Unknown error'));
    exit;
}

if (empty($invoice['invoice']['checkoutLink'])) {
    _error_log('BTC::invoice.php - ERROR: No checkout link in response', AVideoLog::$ERROR);
    _error_log('BTC::invoice.php - Invoice data: ' . json_encode($invoice), AVideoLog::$ERROR);
    forbiddenPage("Invalid invoice response: missing checkout link");
    exit;
}

_error_log('BTC::invoice.php - SUCCESS: Redirecting to checkout link', AVideoLog::$DEBUG);

//var_dump($invoice);exit;

header("Location: {$invoice['invoice']['checkoutLink']}");
