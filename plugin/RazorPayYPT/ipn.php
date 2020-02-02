<?php

// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
_error_log("RazorPayIPN Start");
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$razorpay = AVideoPlugin::loadPluginIfEnabled("RazorPayYPT");
$razorpayObject = AVideoPlugin::getObjectData("RazorPayYPT");

$obj = new stdClass();
$obj->error = true;

$api = $razorpay->start();

$json = file_get_contents('php://input');
$webhookBody = json_decode($json);

_error_log("RazorPayIPN header - " . json_encode($_SERVER));
_error_log("RazorPayIPN Body - {$json}");

try {
    $webhookSignature = $api->utility->header('X-Razorpay-Signature');
} catch (Exception $exc) {
    if (!empty($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'])) {
        $webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];
    }
}

if (empty($webhookSignature)) {
    _error_log("RazorPayIPN ERROR, webhookSignature is empty");
} else {
    $api->utility->verifyWebhookSignature($webhookBody, $webhookSignature, $razorpayObject->webhookSecret);

    if (!empty($webhookBody->payload->subscription)) {

        $users_id = $webhookBody->payload->subscription->notes->users_id;
        $plans_id = $webhookBody->payload->subscription->notes->plans_id;

        if (empty($users_id)) {
            _error_log("RazorPayIPN ERROR, user is empty");
        } else if (empty($plans_id)) {
            _error_log("RazorPayIPN ERROR, plan is empty");
        } else {
            Subscription::renew($users_id, $plans_id);
            _error_log("RazorPayIPN: Executed Renew $users_id, $plans_id");
        }
    } else {
        _error_log("RazorPayIPN ERROR, subscription NOT found");
    }
}
_error_log("RazorPayIPN: " . json_encode($obj));
_error_log("RazorPayIPN: POST " . json_encode($_POST));
_error_log("RazorPayIPN: GET " . json_encode($_GET));
_error_log("RazorPayIPN END");
?>