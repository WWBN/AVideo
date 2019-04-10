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

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("BlockonomicsYPT");
$obj = $plugin->getDataObject();

$addr = $_GET['addr'];
//Match secret for security
if (empty($addr)) {
    echo "Addr empty.";
    return;
}

$order = new BlockonomicsOrder(0);
$obj = $order->getFromAddressFromDb($addr);

die(json_encode($obj));



?>