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
$webhookBody = _json_decode($json);

//_error_log("RazorPayIPN header - " . json_encode($_SERVER));
_error_log("RazorPayIPN Body - {$json}");

if(empty($webhookBody)){
    $obj->msg = "No body";
    _error_log("RazorPayIPN Body - {$obj->msg}");
    die(json_encode($obj));
} else if ($webhookBody->event !== "subscription.charged") {
    _error_log("RazorPayIPN Not a subscription, webhook will be ignored");
} else {

    if (!empty($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'])) {
        $webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];
    }

    if (empty($webhookSignature)) {
        _error_log("RazorPayIPN ERROR, webhookSignature is empty");
    } else {
        _error_log("RazorPayIPN verifyWebhookSignature: $webhookSignature, $razorpayObject->webhookSecret");
        $api->utility->verifyWebhookSignature($json, $webhookSignature, $razorpayObject->webhookSecret);

        if (!empty($webhookBody->payload->subscription)) {

            $users_id = $webhookBody->payload->subscription->entity->notes->users_id;
            $plans_id = $webhookBody->payload->subscription->entity->notes->plans_id;

            if (empty($users_id)) {
                _error_log("RazorPayIPN ERROR, user is empty");
            } else if (empty($plans_id)) {
                _error_log("RazorPayIPN ERROR, plan is empty");
            } else {
                $plugin->addBalance($users_id, $webhookBody->payload->payment->entity->amount / 100, "RazorPay recurrent payment: ", json_encode($webhookBody));
                $renew = Subscription::renew($users_id, $plans_id);
                _error_log("RazorPayIPN: Executed Renew $users_id, $plans_id");
                if(!$renew->error){
                   $obj->error = false;
                }else{
                    $obj->error = $renew->msg;
                }
            }
        } else {
            _error_log("RazorPayIPN ERROR, subscription NOT found");
        }
    }
    _error_log("RazorPayIPN: " . json_encode($obj));
    _error_log("RazorPayIPN: POST " . json_encode($_POST));
    _error_log("RazorPayIPN: GET " . json_encode($_GET));
}
_error_log("RazorPayIPN END");
?>