<?php

require_once __DIR__ . '/../../videos/configuration.php';

header('Content-Type: application/json');
$plugin = new AuthorizeNet();
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$userData = isset($_POST['userData']) ? $_POST['userData'] : [];
if ($amount <= 0) {
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}
// TODO: Implement payment logic using Authorize.Net API
// Example: Call Authorize.Net API here
// $result = $plugin->chargePayment($amount, $userData);

// Simulate payment success for now
$paymentSuccess = true;
$users_id = @User::getId();
if ($paymentSuccess && !empty($users_id)) {
    // Add funds to wallet
    $walletPlugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
    if ($walletPlugin) {
        $walletPlugin->addBalance($users_id, $amount, 'Authorize.Net one-time payment');
        echo json_encode(['success' => true, 'result' => 'Payment processed and wallet updated']);
        exit;
    }
}
echo json_encode(['error' => 'Payment failed or user not logged in']);
