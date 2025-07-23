<?php

use Google\Service\ServiceControl\Auth;

require_once __DIR__ . '/../../videos/configuration.php';
header('Content-Type: application/json');

if(!User::isLogged()){
    forbiddenPage('You must be logged out to access this endpoint');
}

try {
    $obj = AVideoPlugin::getDataObject('AuthorizeNet');
    $users_id = User::getId();

    // ========== Validate payment amount ==========
    if (!empty($_REQUEST['plans_id']) && AVideoPlugin::isEnabledByName('Subscription')) {
       $sp = new SubscriptionPlansTable($_REQUEST['plans_id']);
       $amount = $sp->getPrice();
    }
    if(empty($amount)){
        $amount = isset($_REQUEST['amount']) ? floatval($_REQUEST['amount']) : 0;
    }
    if ($amount <= 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid amount', 'line' => __LINE__]);
        exit;
    }

    // ========== Add optional metadata ==========
    $metadata = [];
    $metadata['users_id'] = User::getId();
    $metadata['plans_id'] = $_REQUEST['plans_id'] ?? 0;

    AuthorizeNet::createWebhookIfNotExists();

    // ========== Process payment via SDK using Accept opaque token + metadata ==========
    $result = AuthorizeNet::generateHostedPaymentPage($amount, $metadata);
    if (!empty($result['success'])) {
        echo json_encode([
            'error' => false,
            'msg'   => 'Payment created successfully',
            'transactionId' => $result['transactionId'],
            'line'  => __LINE__
        ]);
        exit;
    }

    // ========== Return error response if payment fails ==========
    echo json_encode([
        'error' => !isset($result['error']) || !empty($result['error']),
        'msg'   => $result['msg'] ?? '',
        'result'   => $result,
        'line'  => __LINE__,
        'url'   => $result['url'] ?? '',
        'token'   => $result['token'] ?? '',
    ]);
    exit;

} catch (Exception $e) {
    // ========== Return exception error ==========
    echo json_encode([
        'error' => true,
        'msg'   => $e->getMessage(),
        'line'  => __LINE__
    ]);
    exit;
}
