<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->walletBalance = 0;

if (!User::isLogged()) {
    $obj->msg = ("Is not Loged");
    die(json_encode($obj));
}
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
if(empty($plugin)){
    $obj->msg = ("Plugin not enabled");
    die(json_encode($obj));
}
header('Content-Type: application/json');

$wallet = new Wallet(0);
$wallet->setUsers_id(User::getId());
$wallet->setCrypto_wallet_address($_POST['CryptoWallet']);
if($wallet->save()){
    $obj->error = false;
}

if(isset($_REQUEST['donation_notification_url'])){
     $obj->donation_notification_url = YPTWallet::setDonationNotificationURL(User::getId(), $_REQUEST['donation_notification_url']);
}

// Handle webhook secret regeneration
if(isset($_REQUEST['regenerate_webhook_secret']) && $_REQUEST['regenerate_webhook_secret'] == '1'){
    $obj->new_webhook_secret = YPTWallet::regenerateDonationNotificationSecret(User::getId());
}

// Always return current webhook secret
$obj->webhook_secret = YPTWallet::getDonationNotificationSecret(User::getId());

$obj->walletBalance = $plugin->getBalanceFormated(User::getId());

echo json_encode($obj);
