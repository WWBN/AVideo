<?php

require_once '../../videos/configuration.php';
header('Content-Type: application/json');

if (!User::isLogged()) {
    forbiddenPage('Login required');
}
forbidIfInvalidToken();

$response = ['error' => true, 'credited' => false, 'msg' => __('An error occurred')];
try {
    $intentId = (string) ($_POST['payment_intent_id'] ?? '');
    if (!preg_match('/^pi_[a-zA-Z0-9]+$/', $intentId)) {
        throw new InvalidArgumentException('Invalid Stripe payment intent ID');
    }
    $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
    if (empty($wallet)) {
        throw new RuntimeException('Wallet plugin is disabled');
    }
    $response['credited'] = $wallet->isStripePaymentCredited($intentId, User::getId());
    $response['error'] = false;
    $response['msg'] = '';
} catch (Throwable $th) {
    _error_log('Stripe payment credit status: ' . $th->getMessage(), AVideoLog::$ERROR);
}
echo json_encode($response);
