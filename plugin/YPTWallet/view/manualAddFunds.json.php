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

$subject = $config->getWebSiteTitle()." ".$dataObj->manualAddFundsPageButton." from: ".User::getUserName();

$wallet = $plugin->getOrCreateWallet(User::getId());
$wallet_id = $wallet->getId();
$value = floatval($_POST['value']);
$url = "{$global['webSiteRootURL']}plugin/YPTWallet/view/history.php?users_id=".User::getId();
$message = "<strong style='color:#0A0;'>".YPTWallet::MANUAL_ADD."</strong> user <strong><a href='{$url}'>[". User::getId()."]". User::getNameIdentification()."</a></strong> value of {$value}";
$emailMessage = "The user <a href='{$url}'>[". User::getId()."]<strong>". User::getNameIdentification()."</strong></a> request a <strong style='color:#0A0;'>".YPTWallet::MANUAL_ADD."</strong> value of <strong>{$value}</strong>"
. "<hr><strong>Date: </strong>".  date("Y-m-d h:i:s")
. "<br><strong>Informations: </strong>".  nl2br($_POST['informations']);

if(WalletLog::addLog($wallet_id, $value, $message, "{}", "pending",  YPTWallet::MANUAL_ADD)){
    $plugin->sendEmails($emailsArray, $subject, $emailMessage."");
    $obj->error = false;
}
die(json_encode($obj));
