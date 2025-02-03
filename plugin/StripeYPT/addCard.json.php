<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (empty($_POST['paymentMethodId'])) {
    forbiddenPage('Invalid paymentMethodId');
}

if (empty($_REQUEST['subscription_id'])) {
    forbiddenPage('subscription_id not found');
}

if (!User::isLogged()) {
    forbiddenPage('Invalid user');
}

$users_id = User::getId();

$plugin = AVideoPlugin::loadPluginIfEnabled("StripeYPT");

if (empty($plugin)) {
    forbiddenPage('Invalid StripeYPT');
}

$s = new SubscriptionTable($_REQUEST['subscription_id']);

if($s->getUsers_id() != User::getId()){
    forbiddenPage('This plan does not belong to you');
}

$customer_id = $s->getStripe_costumer_id();

if (empty($customer_id)) {
    forbiddenPage('Invalid customer_id');
}

$stripe = AVideoPlugin::loadPlugin("StripeYPT");

$paymentMethod = $stripe->addCard($customer_id, $_POST['paymentMethodId']);

if(!empty($paymentMethod )){
    $subscription = $stripe->userHasActiveSubscriptionOnPlan($s->getSubscriptions_plans_id());
    if(!empty($subscription)){

    }
}

$obj->error = !$stripe->addCard($customer_id, $_POST['paymentMethodId']);
$obj->msg = $addCardErrorMessage;

echo json_encode($obj);
?>
