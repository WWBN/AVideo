<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isLogged()) {
    $obj->msg = ("Is not logged");
    die(json_encode($obj));
}
$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
if(empty($plugin)){
    $obj->msg = ("Plugin not enabled");
    die(json_encode($obj));
}

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
$dataObj = $plugin->getDataObject();
$options = json_decode($dataObj->addFundsOptions);

//send an email
$emailsArray = array();
$emailsArray[] = $dataObj->manualAddFundsNotifyEmail;

$subject = $config->getWebSiteTitle()." ".$dataObj->manualAddFundsPageButton;

$wallet = $plugin->getWallet(User::getId());
$wallet_id = $wallet->getId();
$value = floatval($_POST['value']);

$message = "The user [". User::getId()."]". User::getUserName()." request a manual funds add of {$value}";

if(WalletLog::addLog($wallet_id, $value, $message, "{}", "pending", "Manual Add Funds")){
    $plugin->sendEmails($emailsArray, $subject, $message);
    $obj->error = false;
}
die(json_encode($obj));
