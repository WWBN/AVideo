<?php

// security.php coerces every *_id to an integer during bootstrap. Stripe IDs are
// strings: retain this one field and validate it strictly after auth and CSRF.
$stripeStatusPaymentIntent = isset($_POST['payment_intent_id']) && is_string($_POST['payment_intent_id'])
    ? $_POST['payment_intent_id'] : '';
require_once '../../videos/configuration.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

if (!User::isLogged()) {
    forbiddenPage('Login required');
}
forbidIfInvalidToken();

$response = ['error' => true, 'credited' => false, 'msg' => __('An error occurred')];
try {
    $intentId = $stripeStatusPaymentIntent;
    if (!preg_match('/^pi_[a-zA-Z0-9]+$/', $intentId)) {
        throw new InvalidArgumentException('Invalid Stripe payment intent ID');
    }
    $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
    if (empty($wallet)) {
        throw new RuntimeException('Wallet plugin is disabled');
    }
    $response['credited'] = $wallet->isStripePaymentCredited($intentId, User::getId());
    $videos_id = intval($_POST['videos_id'] ?? 0);
    if ($videos_id > 0) {
        $ppv = AVideoPlugin::loadPluginIfEnabled('PayPerView');
        $response['rental_ready'] = $response['credited'] && !empty($ppv)
            && $ppv->userCanWatchVideo(User::getId(), $videos_id) === 1;
        if ($response['rental_ready']) {
            ObjectYPT::deleteAllSessionCache();
        }
    }
    $scheduleId = intval($_POST['ppvlive_schedule_id'] ?? 0);
    if ($scheduleId > 0) {
        $live = AVideoPlugin::loadPluginIfEnabled('PayPerViewLive');
        $purchase = !empty($live) ? $live->getSubscriptionToPlan(User::getId(), $scheduleId) : false;
        $response['live_ready'] = $response['credited'] && !empty($purchase['id']) && $purchase['status'] === 'a';
        if ($response['live_ready']) {
            $response['live_started'] = empty($purchase['schedule']['future']);
            $response['watch_url'] = $response['live_started']
                ? Live::getLinkToLiveFromUsers_id($purchase['schedule']['users_id'])
                : $global['webSiteRootURL'] . 'plugin/PayPerViewLive/View/buy.php?users_id='
                    . intval($purchase['schedule']['users_id']) . '&ppvlive_schedule_id=' . $scheduleId;
            ObjectYPT::deleteAllSessionCache();
        }
    }
    $response['error'] = false;
    $response['msg'] = '';
} catch (Throwable $th) {
    _error_log('Stripe payment credit status: ' . $th->getMessage(), AVideoLog::$ERROR);
}
echo json_encode($response);
