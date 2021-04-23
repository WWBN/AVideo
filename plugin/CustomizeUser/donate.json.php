<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/captcha.php';
session_write_close();
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$value = floatval($_POST['value']);
if (empty($value)) {
    $obj->msg = "value is empty";
    die(json_encode($obj));
}

if (!User::isLogged()) {
    $obj->msg = "Not logged";
    die(json_encode($obj));
}

$cu = AVideoPlugin::getDataObjectIfEnabled('CustomizeUser');

if (empty($cu)) {
    $obj->msg = "Plugin not enabled";
    die(json_encode($obj));
}

$wallet = AVideoPlugin::loadPluginIfEnabled("YPTWallet");

if (empty($wallet)) {
    $obj->msg = "Plugin wallet not enabled";
    die(json_encode($obj));
}

if(empty($cu->disableCaptchaOnWalletDirectTransferDonation)){
    $valid = Captcha::validation(@$_POST['captcha']);

    if (empty($valid)) {
        $obj->msg = "Invalid captcha";
        die(json_encode($obj));
    }
}

if (!empty($_POST['videos_id'])) {
    $videos_id = intval($_POST['videos_id']);
    if (empty($videos_id)) {
        $obj->msg = "Video id is empty";
        die(json_encode($obj));
    }

    $video = new Video("", "", $videos_id);
    if (empty($video->getFilename())) {
        $obj->msg = "Video does not exists";
        die(json_encode($obj));
    }

    if (YPTWallet::transferBalance(User::getId(), $video->getUsers_id(), $value, "Donation from ".User::getNameIdentification()." to video ($videos_id) " . $video->getClean_title())) {
        $obj->error = false;
        AVideoPlugin::afterDonation(User::getId(), $value, $videos_id, 0);
    }
} else if (!empty($_POST['users_id'])) {

    $users_id = intval($_POST['users_id']);
    if (empty($users_id)) {
        $obj->msg = "User id is empty";
        die(json_encode($obj));
    }

    $user = new User($users_id);
    if (empty($user->getUser())) {
        $obj->msg = "User does not exists";
        die(json_encode($obj));
    }

    if (YPTWallet::transferBalance(User::getId(), $users_id, $value, "Donation from ".User::getNameIdentification()." to Live for  " . $user->getNameIdentificationBd())) {
        $obj->error = false;
        AVideoPlugin::afterDonation(User::getId(), $value, 0, $users_id);
    }
}

$obj->walletBalance = $wallet->getBalance(User::getId());
die(json_encode($obj));
