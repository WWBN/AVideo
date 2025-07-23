<?php
require_once __DIR__ . '/../../videos/configuration.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

try {
    // Check if user is logged in
    if (!User::isLogged()) {
        $obj->msg = "You must be logged in to cancel subscriptions";
        die(json_encode($obj));
    }

    // Check if AuthorizeNet plugin is enabled
    $plugin = AVideoPlugin::loadPluginIfEnabled('AuthorizeNet');
    if (empty($plugin)) {
        $obj->msg = "AuthorizeNet plugin is disabled";
        die(json_encode($obj));
    }

    // Get subscription ID from POST data
    $subscriptionId = $_POST['subscriptionId'] ?? '';

    if (empty($subscriptionId)) {
        $obj->msg = "Missing subscription ID";
        die(json_encode($obj));
    }

    // Verify that this subscription belongs to the current user
    $users_id = User::getId();
    $customerProfileId = AuthorizeNet::getOrCreateCustomerProfile($users_id);

    if (empty($customerProfileId)) {
        $obj->msg = "Customer profile not found";
        die(json_encode($obj));
    }

    // Get subscription details to verify ownership
    $subscriptionResult = AuthorizeNet::getSubscriptionWithCurrentStatus($subscriptionId);

    if ($subscriptionResult['error']) {
        $obj->msg = "Failed to verify subscription: " . $subscriptionResult['msg'];
        die(json_encode($obj));
    }

    // Additional security check: verify the subscription belongs to this customer
    $userSubscriptions = AuthorizeNet::getUserActiveSubscriptions($users_id);
    $subscriptionFound = false;

    if (!$userSubscriptions['error']) {
        foreach ($userSubscriptions['subscriptions'] as $sub) {
            if ($sub['subscriptionId'] === $subscriptionId) {
                $subscriptionFound = true;
                break;
            }
        }
    }

    if (!$subscriptionFound) {
        $obj->msg = "Subscription not found or does not belong to current user";
        die(json_encode($obj));
    }

    // Cancel the subscription
    $cancelResult = AuthorizeNet::cancelSubscription($subscriptionId);

    if ($cancelResult['error']) {
        $obj->msg = $cancelResult['msg'];
        die(json_encode($obj));
    }

    // Success response
    $obj->error = false;
    $obj->msg = "Subscription canceled successfully";
    $obj->subscriptionId = $subscriptionId;
    $obj->status = $cancelResult['status'];

} catch (Exception $e) {
    $obj->msg = "An error occurred: " . $e->getMessage();
    _error_log("[AuthorizeNet] Error in cancelSubscription.json.php: " . $e->getMessage());
}

echo json_encode($obj);
?>
