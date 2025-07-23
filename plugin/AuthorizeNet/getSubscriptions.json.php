<?php
require_once __DIR__ . '/../../videos/configuration.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->subscriptions = [];

try {
    // Check if user is logged in
    if (!User::isLogged()) {
        $obj->msg = "You must be logged in to view subscriptions";
        die(json_encode($obj));
    }

    // Check if AuthorizeNet plugin is enabled
    $plugin = AVideoPlugin::loadPluginIfEnabled('AuthorizeNet');
    if (empty($plugin)) {
        $obj->msg = "AuthorizeNet plugin is disabled";
        die(json_encode($obj));
    }

    $users_id = User::getId();

    // Get customer profile
    $customerProfileId = AuthorizeNet::getOrCreateCustomerProfile($users_id);

    if (empty($customerProfileId)) {
        $obj->msg = "Customer profile not found";
        die(json_encode($obj));
    }

    // Get all subscriptions for this customer from Authorize.Net API
    $subscriptionsResult = AuthorizeNet::getCustomerSubscriptions($customerProfileId);

    if ($subscriptionsResult['error']) {
        $obj->msg = $subscriptionsResult['msg'];
        die(json_encode($obj));
    }

    $subscriptionsWithStatus = [];

    // Get current status for each subscription
    foreach ($subscriptionsResult['subscriptions'] as $subscription) {
        $subscriptionId = $subscription['subscriptionId'];

        // Get detailed subscription info with current status
        $detailsResult = AuthorizeNet::getSubscriptionWithCurrentStatus($subscriptionId);

        if (!$detailsResult['error'] && !empty($detailsResult['subscription'])) {
            $subscriptionsWithStatus[] = $detailsResult['subscription'];
        }
    }

    // Sort subscriptions by creation date (newest first)
    usort($subscriptionsWithStatus, function($a, $b) {
        return $b['subscriptionId'] - $a['subscriptionId'];
    });

    // Success response
    $obj->error = false;
    $obj->subscriptions = $subscriptionsWithStatus;
    $obj->total = count($subscriptionsWithStatus);

} catch (Exception $e) {
    $obj->msg = "An error occurred: " . $e->getMessage();
    _error_log("[AuthorizeNet] Error in getSubscriptions.json.php: " . $e->getMessage());
}

echo json_encode($obj);
?>
