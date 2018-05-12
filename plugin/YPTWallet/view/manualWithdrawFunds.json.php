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
$dataObj = $plugin->getDataObject();
if(empty($plugin)){
    $obj->msg = ("Plugin not enabled");
    die(json_encode($obj));
}

$value = floatval($_POST['value']);

if($plugin->transferBalance(User::getId(),$dataObj->manualWithdrawFundsTransferToUserId, $value)){
    //send an email
    $emailsArray = array();
    $emailsArray[] = $dataObj->manualWithdrawFundsNotifyEmail;

    $subject = $config->getWebSiteTitle()." ".$dataObj->manualWithdrawFundsPageButton." from: ".User::getUserName();

    $wallet = $plugin->getOrCreateWallet(User::getId());
    $wallet_id = $wallet->getId();
    $url = "{$global['webSiteRootURL']}plugin/YPTWallet/view/history.php?users_id=".User::getId();
    $message = "<strong style='color:#A00;'>".YPTWallet::MANUAL_WITHDRAW."<strong> user <strong><a href='{$url}'>[". User::getId()."]". User::getUserName()."</a></strong> value of {$value}";
    $emailMessage = "The user <a href='{$url}'>[". User::getId()."]<strong>". User::getUserName()."</strong></a> request a <strong style='color:#A00;'>".YPTWallet::MANUAL_WITHDRAW."</strong> value of <strong>{$value}</strong>"
    . "<hr><strong>Date: </strong>".  date("Y-m-d h:i:s")
    . "<br><strong>Informations: </strong>".  nl2br($_POST['informations']);

    if(WalletLog::addLog($wallet_id, $value, $message, "{}", "pending", YPTWallet::MANUAL_WITHDRAW)){
        $plugin->sendEmails($emailsArray, $subject, $emailMessage."");
        $obj->error = false;
    }else{
        $obj->msg = "Something is wrong, contact the admin";
    }
}else{
    $obj->msg = "We could not transfer funds, please check your balance";
}

$obj->walletBalance = $plugin->getBalanceFormated(User::getId());
die(json_encode($obj));
