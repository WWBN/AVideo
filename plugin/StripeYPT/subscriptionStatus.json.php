<?php
require_once '../../videos/configuration.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
if (!User::isLogged()) {
    forbiddenPage('Login required');
}
forbidIfInvalidToken();

$response = ['error' => true, 'active' => false, 'msg' => __('An error occurred')];
try {
    $plans_id = intval($_POST['plans_id'] ?? 0);
    $subscription = AVideoPlugin::loadPluginIfEnabled('Subscription');
    if (empty($subscription) || $plans_id <= 0) {
        throw new InvalidArgumentException('Invalid subscription plan');
    }
    $access = false;
    if (method_exists($subscription, 'getCheckoutAccess')) {
        $access = $subscription->getCheckoutAccess(User::getId(), $plans_id);
    } else {
        // Independently distributed Subscription releases expose this older API.
        foreach ($subscription->getAllFromUser(User::getId(), true) as $row) {
            if (intval($row['subscriptions_plans_id']) === $plans_id && strtotime($row['good_until']) > time()) {
                $access = $row;
                break;
            }
        }
    }
    $response['active'] = !empty($access);
    if ($response['active']) {
        // The webhook has a separate session; evict this browser's pre-purchase denial.
        ObjectYPT::deleteAllSessionCache();
    }
    $response['good_until'] = $access['good_until'] ?? '';
    $response['error'] = false;
    $response['msg'] = '';
} catch (Throwable $th) {
    _error_log('Subscription checkout status: ' . $th->getMessage(), AVideoLog::$ERROR);
}
echo json_encode($response);
