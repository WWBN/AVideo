<?php
require_once __DIR__ . '/../../videos/configuration.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

try {
    // Check if user is logged in
    if (!User::isLogged()) {
        $obj->msg = "You must be logged in to check subscription status";
        die(json_encode($obj));
    }

    // Check if AuthorizeNet plugin is enabled
    $plugin = AVideoPlugin::loadPluginIfEnabled('AuthorizeNet');
    if (empty($plugin)) {
        $obj->msg = "AuthorizeNet plugin is disabled";
        die(json_encode($obj));
    }

    // Get subscription ID from GET data
    $subscriptionId = $_GET['subscriptionId'] ?? '';

    if (empty($subscriptionId)) {
        $obj->msg = "Missing subscription ID";
        die(json_encode($obj));
    }

    // Verify ownership (similar to cancel endpoint)
    $users_id = User::getId();
    $customerProfileId = AuthorizeNet::getOrCreateCustomerProfile($users_id);

    if (empty($customerProfileId)) {
        $obj->msg = "Customer profile not found";
        die(json_encode($obj));
    }

    // Get subscription with current status
    $subscriptionResult = AuthorizeNet::getSubscriptionWithCurrentStatus($subscriptionId);

    if ($subscriptionResult['error']) {
        $obj->msg = $subscriptionResult['msg'];
        die(json_encode($obj));
    }

    // Verify ownership
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

    // Success response
    $obj->error = false;
    $obj->subscription = $subscriptionResult['subscription'];
    $obj->status = $subscriptionResult['subscription']['currentStatus'];
    $obj->isActive = $subscriptionResult['subscription']['isActive'];

} catch (Exception $e) {
    $obj->msg = "An error occurred: " . $e->getMessage();
    _error_log("[AuthorizeNet] Error in getSubscriptionStatus.json.php: " . $e->getMessage());
}

echo json_encode($obj);
?>
