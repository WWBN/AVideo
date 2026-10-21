<?php
require_once $global['systemRootPath'].'plugin/AVideoPlugin.php';
$obj = AVideoPlugin::getObjectData("PayPalYPT");

/**
 * Credentials come from your own REST app at https://developer.paypal.com/dashboard/applications
 * (Sandbox or Live, matching the disableSandbox setting). See plugin/PayPalYPT/README.md.
 */
$apiContext = PayPalYPT::getApiContext(null, $obj);
