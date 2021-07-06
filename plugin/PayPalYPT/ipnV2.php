<?php

// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
_error_log("PayPalIPN V2 Start");
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

$redirectUri = YPTWallet::getAddFundsSuccessRedirectURL();
if (empty($redirectUri)) {
    $redirectUri = getRedirectUri();
}

if (empty($plugin)) {
    $obj->msg = 'Wallet Disabled';
    die(json_encode($obj));
}

if (empty($paypal)) {
    $obj->msg = 'PayPal Disabled';
    die(json_encode($obj));
}

if (empty($_REQUEST['success'])) {
    header('Location: ' . $redirectUri);
    exit;
}

_error_log("PayPalIPN V2: " . json_encode($obj));
_error_log("PayPalIPN V2: POST " . json_encode($_POST));
_error_log("PayPalIPN V2: GET " . json_encode($_GET));

$json = _json_decode(@$_GET['json']);
if (User::isLogged()) {
    $json->users_id = User::getId();
}

if (!empty($_GET['token'])) {
    _error_log("PayPalIPN V2: token {$_GET['token']} ");
    if (!PayPalYPT::isTokenUsed($_GET['token'])) {
        _error_log("PayPalIPN V2: token will be processed ");
        $agreement = $paypal->execute();
        $payment_amount = floatval($agreement->agreement_details->last_payment_amount->value);
        $payment_currency = $agreement->agreement_details->last_payment_amount->currency;
        //$payment_time = strtotime($agreement->agreement_details->last_payment_date);

        $pp = new PayPalYPT_log(0);
        $pp->setUsers_id($json->users_id);
        $pp->setAgreement_id($agreement->id);
        $pp->setToken($_GET['token']);
        $pp->setValue($payment_amount);
        $pp->setJson(array('agreement' => $agreement, 'post' => $_POST, 'get' => $_GET));
    } else {
        _error_log("PayPalIPN V2: token was already processed ");
    }
    //var_dump($agreement, date('Y-m-d\TH:i:s'));
    //exit;
} else {
    $ipn = PayPalYPT::IPNcheck();
    if (!$ipn) {
        $obj->msg = 'IPN Fail';
        _error_log("PayPalIPN V2: IPN Fail ");
        die(json_encode($obj));
    }
    _error_log("PayPalIPN V2: else ");
    if (!PayPalYPT::isRecurringPaymentIdUsed($_POST["verify_sign"])) {
        _error_log("PayPalIPN V2: verify_sign will be processed ");
        $payment_amount = empty($_POST['mc_gross']) ? $_POST['amount'] : $_POST['mc_gross'];
        $payment_currency = empty($_POST['mc_currency']) ? $_POST['currency_code'] : $_POST['mc_currency'];

        $pp = new PayPalYPT_log(0);
        $pp->setUsers_id($json->users_id);
        $pp->setRecurring_payment_id($_POST["verify_sign"]);
        $pp->setValue($payment_amount);
        $pp->setJson(array('ipn' => $ipn, 'post' => $_POST, 'get' => $_GET));
    } else {
        _error_log("PayPalIPN V2: verify_sign was already processed ");
    }
}
if (!empty($pp) && is_object($pp)) {
    if ($pp->save()) {
        if ($walletObject->currency === $payment_currency) {
            _error_log("PayPalIPN V2: token log saved $json->users_id, $payment_amount");
            if (!empty($payment_amount) && !empty($json->users_id)) {
                $plugin->addBalance($json->users_id, $payment_amount, "PayPal Subscription from token", json_encode($_POST));
            } else {
                _error_log("PayPalIPN V2: ERROR balance not added");
            }
        } else {
            _error_log("PayPalIPN V2: Invalid currency $walletObject->currency===$payment_currency ");
        }
    } else {
        _error_log("PayPalIPN V2: FAIL to save log");
    }
}
if (!empty($json->type)) {
    switch ($json->type) {
        case 'FansSubscriptions':
            _error_log("PayPalIPN V2 FansSubscriptions");
            if (!empty($json->Fsubscriptions_plan_id) && !empty($json->users_id)) {
                $fsObj = AVideoPlugin::getDataObjectIfEnabled('FansSubscriptions');
                if (!empty($fsObj) && !empty($json->users_id)) {
                    if (FansSubscriptions::renew($json->users_id, $json->Fsubscriptions_plan_id)) {
                        header('Location: ' . $redirectUri);
                        exit;
                    }
                }
            }
            break;
    }
}

_error_log("PayPalIPN V2 END");
?>