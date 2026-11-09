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

// Notifications hold payer names and e-mails: log only safe identifiers.
_error_log('PayPalIPN V2: txn_type=' . preg_replace('/[^a-z_]/i', '', (string) ($_POST['txn_type'] ?? ''))
    . ' token=' . preg_replace('/[^A-Z0-9_-]/i', '', (string) ($_GET['token'] ?? '')));

$json = _json_decode(@$_GET['json']);
if (User::isLogged()) {
    $json->users_id = User::getId();
}

if (!empty($_GET['token'])) {
    _error_log("PayPalIPN V2: token {$_GET['token']} ");
    if (!PayPalYPT::isTokenUsed($_GET['token'])) {
        _error_log("PayPalIPN V2: token will be processed ");
        $agreement = $paypal->execute();
        //_error_log("PayPalIPN V2: agreement ". print_r($agreement->getAgreementDetails()->getLastPaymentAmount()->getValue(), true));

        if (empty($agreement) || $agreement->getState() !== 'Active') {
            $obj->msg = 'The Agreement is not active yet ';
            _error_log("PayPalIPN V2: {$obj->msg} ");
            die(json_encode($obj));
        }

        $lastPayment = $agreement->getAgreementDetails()->getLastPaymentAmount();
        if (empty($lastPayment)) {
            $lastPayment = $agreement->getPlan()->getMerchantPreferences()->getSetupFee();
        }
        if (empty($lastPayment)) {
            _error_log('PayPalIPN V2: agreement without a payment amount id=' . preg_replace('/[^A-Z0-9_-]/i', '', (string) $agreement->getId()), AVideoLog::$ERROR);
            $obj->msg = 'The Agreement has no payment yet';
            die(json_encode($obj));
        }
        $payment_amount = floatval($lastPayment->getValue());
        $payment_currency = $lastPayment->getCurrency();
        //$payment_time = strtotime($agreement->agreement_details->last_payment_date);

        $pp = new PayPalYPT_log(0);
        $pp->setUsers_id($json->users_id);
        $pp->setAgreement_id($agreement->id);
        $pp->setToken($_GET['token']);
        $pp->setValue($payment_amount);
        $pp->setJson(['post' => $_POST, 'get' => $_GET]);
    } else {
        _error_log("PayPalIPN V2: token was already processed ");
    }
    //var_dump($agreement, date('Y-m-d\TH:i:s'));
    //exit;
} else {
    // GHSA-5gg3-9jhh-xf86: only PayPal's browser return (with its token) is handled here. PayPal sends the
    // agreement's IPNs to the notify_url, which has no "success" and is redirected above, so a notification
    // only reached this point in a crafted request. Crediting the unsigned $_GET['json'] user from it let any
    // PayPal-verified IPN, even one sent to another merchant, mint wallet balance.
    _error_log('PayPalIPN V2: ignored a request without a PayPal token', AVideoLog::$SECURITY);
    $obj->msg = 'Invalid request';
    die(json_encode($obj));
}
$paymentRecorded = false;
if (!empty($pp) && is_object($pp)) {
    $savePayment = function () use ($pp, $walletObject, $payment_currency, $payment_amount, $json, $plugin, &$paymentRecorded) {
        global $global;
        $hadOriginBypass = array_key_exists('bypassSameDomainCheck', $global);
        $previousOriginBypass = $global['bypassSameDomainCheck'] ?? null;
        try {
            $global['bypassSameDomainCheck'] = 1;
            if ($walletObject->currency !== $payment_currency || !is_finite($payment_amount)
                || $payment_amount <= 0 || empty($json->users_id)) {
                _error_log('PayPalIPN V2: invalid wallet credit', AVideoLog::$ERROR);
                return false;
            }
            if (!$pp->save()) {
                _error_log("PayPalIPN V2: FAIL to save log");
                return false;
            }
            $plugin->addBalance($json->users_id, $payment_amount, "PayPal Subscription from token", json_encode($_POST));
            $paymentRecorded = true;
            return true;
        } finally {
            if ($hadOriginBypass) {
                $global['bypassSameDomainCheck'] = $previousOriginBypass;
            } else {
                unset($global['bypassSameDomainCheck']);
            }
        }
    };
    try {
        // Keep the token log and wallet credit atomic; a duplicate must not authorize a renewal.
        $plugin->processPaymentOnce('paypal', ['token:' . $_GET['token']], $json->users_id, $savePayment, [
            'legacyCheck' => function () { return PayPalYPT::isTokenUsed($_GET['token']); }
        ]);
    } catch (Throwable $error) {
        $paymentRecorded = false;
        _error_log('PayPalIPN V2: payment could not be recorded', AVideoLog::$ERROR);
        $obj->msg = 'Payment could not be recorded';
        die(json_encode($obj));
    }
}
// The renewal is paid from the subscriber's wallet: run it only in the request that recorded this token's payment,
// and only for the logged-in buyer. A reused token or an edited $_GET['json'] must not spend another user's balance.
if ($paymentRecorded && User::isLogged() && !empty($json->type)) {
    switch ($json->type) {
        case 'FansSubscriptions':
            _error_log("PayPalIPN V2 FansSubscriptions");
            if (!empty($json->Fsubscriptions_plan_id) && !empty($json->users_id)) {
                $fsObj = AVideoPlugin::getDataObjectIfEnabled('FansSubscriptions');
                if (!empty($fsObj) && !empty($json->users_id)) {
                    $hadOriginBypass = array_key_exists('bypassSameDomainCheck', $global);
                    $previousOriginBypass = $global['bypassSameDomainCheck'] ?? null;
                    try {
                        $global['bypassSameDomainCheck'] = 1;
                        $renew = FansSubscriptions::renew($json->users_id, $json->Fsubscriptions_plan_id);
                    } finally {
                        if ($hadOriginBypass) {
                            $global['bypassSameDomainCheck'] = $previousOriginBypass;
                        } else {
                            unset($global['bypassSameDomainCheck']);
                        }
                    }
                    if ($renew) {
                        $redirectUri = addQueryStringParameter($redirectUri, 'msg', $renew->msg);
                        header('Location: ' . $redirectUri);
                        exit;
                    }
                }
            }
            break;
    }
}

_error_log("PayPalIPN V2 END");
