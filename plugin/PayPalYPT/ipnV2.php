<?php
// check recurrent payments
header('Content-Type: application/json');

$paypalIPNTransactionId = $_POST['txn_id'] ?? '';
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
$paypalPaymentIds = null;
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
    $ipn = PayPalYPT::IPNcheck();
    if (!$ipn) {
        $obj->msg = 'IPN Fail';
        _error_log("PayPalIPN V2: IPN Fail ");
        die(json_encode($obj));
    }
    _error_log("PayPalIPN V2: else ");
    // Restore only after verification, and share the sale ID with IPN v1 and the REST webhook.
    $_POST['txn_id'] = $paypalIPNTransactionId;
    if (($_POST['txn_type'] ?? '') !== 'recurring_payment' || ($_POST['payment_status'] ?? '') !== 'Completed') {
        $obj->error = false;
        die(json_encode($obj));
    }
    $paypalPaymentIds = [(string) (!empty($_POST['txn_id']) ? $_POST['txn_id'] : ($_POST['verify_sign'] ?? ''))];
    $payment_amount = empty($_POST['mc_gross']) ? $_POST['amount'] : $_POST['mc_gross'];
    $payment_currency = empty($_POST['mc_currency']) ? $_POST['currency_code'] : $_POST['mc_currency'];

    $pp = new PayPalYPT_log(0);
    $pp->setUsers_id($json->users_id);
    $pp->setRecurring_payment_id(substr($paypalPaymentIds[0], 0, 45));
    $pp->setValue($payment_amount);
    $pp->setJson(['ipn' => $ipn, 'post' => $_POST, 'get' => $_GET]);
}
if (!empty($pp) && is_object($pp)) {
    $savePayment = function () use ($pp, $walletObject, $payment_currency, $payment_amount, $json, $plugin) {
        global $global;
        $hadOriginBypass = array_key_exists('bypassSameDomainCheck', $global);
        $previousOriginBypass = $global['bypassSameDomainCheck'] ?? null;
        try {
            $global['bypassSameDomainCheck'] = 1;
            if (!$pp->save()) {
                _error_log("PayPalIPN V2: FAIL to save log");
                return false;
            }
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
            return true;
        } finally {
            if ($hadOriginBypass) {
                $global['bypassSameDomainCheck'] = $previousOriginBypass;
            } else {
                unset($global['bypassSameDomainCheck']);
            }
        }
    };
    if ($paypalPaymentIds !== null) {
        $processed = false;
        try {
            $plugin->processPaymentOnce('paypal', $paypalPaymentIds, $json->users_id,
                function () use ($savePayment, &$processed) {
                    $processed = $savePayment();
                    return $processed;
                }, ['legacyCheck' => function () use ($paypalPaymentIds, $plugin, $json) {
                    return PayPalYPT::isRecurringPaymentIdUsed($paypalPaymentIds[0])
                        || (!empty($_POST['verify_sign']) && (PayPalYPT::isRecurringPaymentIdUsed($_POST['verify_sign'])
                            || WalletLog::hasProcessedPayment('paypal', [(string) $_POST['verify_sign']],
                                $plugin->getOrCreateWallet($json->users_id)->getId(),
                                ['legacyDescriptions' => ['Paypal recurrent%', 'PayPal Subscription from token%']])));
                }, 'legacyDescriptions' => ['Paypal recurrent%', 'PayPal Subscription from token%']]);
            $obj->error = false;
        } catch (\Throwable $error) {
            _error_log('PayPalIPN V2: payment processing failed; retry delivery: ' . $error->getMessage(), AVideoLog::$ERROR);
            http_response_code(500);
            $obj->msg = 'Payment could not be processed';
            die(json_encode($obj));
        }
        if (!$processed) {
            die(json_encode($obj));
        }
    } else {
        $savePayment();
    }
}
if (!empty($json->type)) {
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
