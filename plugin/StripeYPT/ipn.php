<?php
// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
error_log("StripeIPN Start");
$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = YouPHPTubePlugin::getObjectData("YPTWallet");
$stripe = YouPHPTubePlugin::loadPluginIfEnabled("StripeYPT");

//error_log("StripeIPN: ".json_encode($obj));
error_log("StripeIPN: POST ".json_encode($_POST));
error_log("StripeIPN: GET ".json_encode($_GET));
error_log("StripeIPN END");

?>