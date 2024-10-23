<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/captcha.php';
_session_write_close();
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->extraParameters = new stdClass();
$value = floatval($_REQUEST['value']);
if (empty($value)) {
    if (isset($_REQUEST['buttonIndex'])) {
        if (!empty($_REQUEST['videos_id'])) {
            $videos_id = intval($_REQUEST['videos_id']);
            if (!empty($videos_id)) {
                $video = new Video("", "", $videos_id);
                $users_id = intval($video->getUsers_id());
            }
        }
        if (empty($users_id)) {
            $users_id = intval(@$_REQUEST['users_id']);
        }

        if (!empty($users_id)) {
            $donationButtons = User::getDonationButtons($users_id);
            $value = 0;
            foreach ($donationButtons as $value) {
                if ($value->index == $_REQUEST['buttonIndex']) {
                    $obj->extraParameters = $value;
                    $value = floatval($value->value);
                    break;
                }
            }
        }
    }
    if (empty($value)) {
        $obj->msg = "value is empty";
        die(json_encode($obj));
    }
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

if (empty($cu->disableCaptchaOnWalletDirectTransferDonation)) {
    $valid = Captcha::validation(@$_REQUEST['captcha']);

    if (empty($valid)) {
        $obj->msg = "Invalid captcha";
        die(json_encode($obj));
    }
}
$obj->extraParameters->superChat = 0;
$obj->extraParameters->message = $_REQUEST['message'];

if (!empty($_REQUEST['videos_id'])) {
    $videos_id = intval($_REQUEST['videos_id']);
    if (empty($videos_id)) {
        $obj->msg = "Video id is empty";
        die(json_encode($obj));
    }

    $video = new Video("", "", $videos_id);
    if (empty($video->getFilename())) {
        $obj->msg = "Video does not exists";
        die(json_encode($obj));
    }

    if (YPTWallet::transferBalance(User::getId(), $video->getUsers_id(), $value, "Donation from " . User::getNameIdentification() . " to video ($videos_id) message: {$obj->extraParameters->message}" . $video->getClean_title())) {
        $obj->error = false;
        $obj->extraParameters->superChat = $value;
        AVideoPlugin::afterDonation(User::getId(), $value, $videos_id, 0, $obj->extraParameters);
    }
} elseif (!empty($_REQUEST['users_id'])) {
    $users_id = intval($_REQUEST['users_id']);
    if (empty($users_id)) {
        $obj->msg = "User id is empty";
        die(json_encode($obj));
    }

    $user = new User($users_id);
    if (empty($user->getUser())) {
        $obj->msg = "User does not exists";
        die(json_encode($obj));
    }
    
    $obj->extraParameters->live_transmitions_history_id = intval(@$_REQUEST['live_transmitions_history_id']);

    if (YPTWallet::transferBalance(User::getId(), $users_id, $value, "Donation from " . User::getNameIdentification() . " to Live for  " . $user->getNameIdentificationBd(). " message: {$obj->extraParameters->message}")) {
        $obj->error = false;
        $obj->extraParameters->superChat = $value;
        AVideoPlugin::afterDonation(User::getId(), $value, 0, $users_id, $obj->extraParameters);
    }
}

$obj->walletBalance = $wallet->getBalance(User::getId());
die(json_encode($obj));
