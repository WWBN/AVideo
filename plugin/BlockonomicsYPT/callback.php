<?php

/**
  -2 : PAYMENT_EXPIRED
  -1 : PAYMENT_ERROR (Happens when Paid BTC amount is not matching expected value)
  0 : UNPAID
  1 : IN_PROCESS
  2 : PAID
 */
// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("BlockonomicsYPT");
$obj = $plugin->getDataObject();

$txid = $_GET['txid'];
$value = $_GET['value'];
$status = $_GET['status'];
$addr = $_GET['addr'];
//Match secret for security
if ($_GET['secret'] != $obj->Secret) {
    _error_log("Blockonomics Callback: secret is not matching", AVideoLog::$SECURITY);
    echo "Secret is not matching.";
    return;
}

$order = new BlockonomicsOrder(0);
$order->loadFromAddress($addr);

if (empty($order->getId())) {
    _error_log("Blockonomics Callback: address not found addr=" . preg_replace('/[^a-z0-9]/i', '', (string) $addr), AVideoLog::$WARNING);
    echo "Address not found.";
    return;
}

if ($order->getStatus() < -1) {
    //payment already in error/expired, do nothing
    return;
}
$new_status = $status;
if ($status == 0 && time() > strtotime($order->getCreated()) + $obj->ExpireInSeconds) {
    //Payment expired, Paid after 10 minutes
    $new_status = -3;
    print('expired');
}
if ($status == 2 && $value < $order->getBits()) {
    //Payment error, amount paid not matching expected
    $new_status = -2;
}

$order->setTxid($txid);
$order->setBits_payed($value);

// add balance on the wallet
if ($new_status == 2 && $order->getStatus()!=200) {
    $plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
    $users_id = $order->getUsers_id();
    $total = $order->getTotal_value();
    $plugin->addBalance($users_id, $total, "Blockonomics payment", json_encode($order));
    // status OK, do not process it anymore
    $new_status = 200;
}

$order->setStatus($new_status);

$order->save();

// txid/addr/value are public blockchain data; the callback secret is never logged.
_error_log("Blockonomics Callback: processed status=" . intval($new_status) . " received_status=" . intval($status)
    . " order_id=" . intval($order->getId()) . " users_id=" . intval($order->getUsers_id())
    . " txid=" . preg_replace('/[^a-z0-9]/i', '', (string) $txid) . " addr=" . preg_replace('/[^a-z0-9]/i', '', (string) $addr)
    . " value=" . floatval($value));
?>