<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (empty($_POST['stripe_costumer_id'])) {
    $obj->msg = "Empty Stripe Costumer ID";
    die(json_encode($obj));
}

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("StripeYPT");


$subs = SubscriptionTable::getFromStripeCostumerId($_POST['stripe_costumer_id']);
if(empty($subs)){
    $obj->msg = "Subscription row not found";
    die(json_encode($obj));
}
$plans_id = $subs['subscriptions_plans_id'];

$subscription = StripeYPT::getSubscriptions($_POST['stripe_costumer_id'], $plans_id);

if(empty($subscription)){
    $obj->msg = "Subscription not found";
    die(json_encode($obj));
}

if (!User::isAdmin() && $subscription->metadata->users_id != User::getId()) {
    $obj->msg = "You Can Not do this";
    die(json_encode($obj));
}

$obj->error = false;
$obj->msg = "";
$obj->response  = $subscription->cancel();
if(!empty($obj->response)){
    SubscriptionTable::updateStripeCostumerId($subs['id'], "canceled");
}
die(json_encode($obj));
?>