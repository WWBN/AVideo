<?php
require_once __DIR__ . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AuthorizeNet/AuthorizeNet.php';
$global['bypassSameDomainCheck'] = 1;

$rawBody = file_get_contents('php://input');
$headers = getallheaders();

// 1) Parse + signature
$parsed = AuthorizeNet::parseWebhookRequest($rawBody, $headers);
if (!empty($parsed['error'])) {
    _error_log('[Authorize.Net webhook] ' . $parsed['msg']);
    http_response_code(200);
    echo $parsed['msg'] ?? 'ignored';
    exit;
}

_error_log('[Authorize.Net webhook] Event: ' . $parsed['eventType']);
_error_log('[Authorize.Net webhook] uniq_key: ' . $parsed['uniq_key']);

// 2) Dedup
if (Anet_webhook_log::alreadyProcessed($parsed['uniq_key'])) {
    _error_log('[Authorize.Net webhook] Duplicate ignored');
    http_response_code(200);
    echo 'duplicate';
    exit;
}

// 3) Fetch txn (fallback/confirm)
$txnInfo = AuthorizeNet::getTransactionDetails($parsed['transactionId']);

// 4) Block only if both invalid signature AND no valid txn info
if (!$parsed['signatureValid'] && (empty($txnInfo) || !empty($txnInfo['error']))) {
    _error_log('[Authorize.Net webhook] Bad signature and could not confirm transaction');
    http_response_code(401);
    echo 'invalid signature';
    exit;
}

// 5) Analyze payload + raw txn
$analysis = AuthorizeNet::analyzeTransactionFromWebhook($parsed['payload'], $txnInfo['raw'] ?? null);

// Fill missing basics from txnInfo if needed
if (!$analysis['users_id'] && !empty($txnInfo['users_id'])) {
    $analysis['users_id'] = (int)$txnInfo['users_id'];
}
if (!$analysis['amount'] && isset($txnInfo['amount'])) {
    $analysis['amount'] = (float)$txnInfo['amount'];
}
if (!$analysis['currency'] && !empty($txnInfo['currency'])) {
    $analysis['currency'] = $txnInfo['currency'];
}
if (!$analysis['isApproved'] && !empty($txnInfo['isApproved'])) {
    $analysis['isApproved'] = (bool)$txnInfo['isApproved'];
}
if (!$analysis['plans_id'] && !empty($txnInfo['plans_id'])) {
    $analysis['plans_id'] = (int)$txnInfo['plans_id'];
}
_error_log('[Authorize.Net webhook] Analysis: ' . json_encode($analysis));

if(empty($analysis['users_id']) || empty($analysis['amount'])) {
    _error_log('[Authorize.Net webhook] Missing user ID or amount in analysis');
    http_response_code(400);
    echo 'missing user ID or amount';
    exit;
}

$result = AuthorizeNet::processSinglePayment(
            $analysis['users_id'],
            (float)$analysis['amount'],
            $parsed['uniq_key'],
            $parsed['eventType'],
            $parsed['payload'],
            !empty($analysis['plans_id'])? "Authorize.Net subscription charge plan [{$analysis['plans_id']}]" : 'Authorize.Net one-time payment'
        );

if (!empty($result['error'])) {
    _error_log('[Authorize.Net webhook] Processing error: ' . $result['msg']);
    http_response_code(500);
    echo json_encode($result);
    exit;
}

// 8) Create subscription if needed
$subscriptionResult = null;
if (!empty($analysis['plans_id'])) {
    _error_log('[Authorize.Net webhook] Creating subscription for plans_id: ' . $analysis['plans_id']);
    // Check if user already has an active subscription for this plan
    $existingCheck = AuthorizeNet::checkUserActiveSubscriptions(
        $analysis['users_id'],
        $analysis['plans_id']
    );

    if (!$existingCheck['error'] && $existingCheck['hasActivePlanSubscription']) {
        _error_log('[Authorize.Net webhook] User already has active subscription for plan: ' . $analysis['plans_id']);
        $subscriptionResult = [
            'error' => false,
            'subscriptionId' => 'existing',
            'msg' => 'User already has active subscription for this plan',
            'existingSubscriptions' => $existingCheck['activeSubscriptions']
        ];
    } else {

        $sp = new SubscriptionPlansTable($analysis['plans_id']);
        $subscription_name = $sp->getName() ?? 'Subscription';

        // Proceed with creating new subscription
        $subscriptionMetadata = [
            'users_id' => (int)$analysis['users_id'],
            'plans_id' => (int)$analysis['plans_id'],
            'subscription_name' => $subscription_name,
            'initial_payment_id' => $parsed['transactionId']
        ];

        $interval = (int)($sp->getHow_many_days() ?? 30);
        $intervalUnit = 'days';

        $subscriptionResult = AuthorizeNet::createSubscription(
            $analysis['users_id'],
            $analysis['amount'],
            $subscriptionMetadata,
            $interval,
            $intervalUnit
        );

        Subscription::renew($analysis['users_id'], $analysis['plans_id'], SubscriptionTable::$gatway_authorize, $subscriptionResult['subscriptionId'], $subscriptionResult);

        if (!empty($subscriptionResult['error'])) {
            _error_log('[Authorize.Net webhook] Subscription creation failed: ' . $subscriptionResult['msg']);
            // Don't fail the entire webhook - the payment was processed successfully
        } else {
            _error_log('[Authorize.Net webhook] Subscription created: ' . $subscriptionResult['subscriptionId']);
        }
    }
}

// 9) Return success response
echo json_encode([
    'success'           => true,
    'users_id'          => $analysis['users_id'],
    'subscription'      => $analysis['isASubscription'],
    'subscriptionId'    => $analysis['subscriptionId'] ?? ($subscriptionResult['subscriptionId'] ?? null),
    'newSubscription'   => $shouldCreateSubscription,
    'subscriptionCreated' => !empty($subscriptionResult) && empty($subscriptionResult['error']),
    'sigValid'          => $parsed['signatureValid'],
    'logId'             => $result['logId'] ?? null
]);
