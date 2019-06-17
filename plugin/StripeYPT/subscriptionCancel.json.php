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

$subscription = StripeYPT::getSubscriptions($_POST['stripe_costumer_id'], $_POST['plans_id']);

if(empty($subscription)){
    $obj->msg = "Subscription not found";
    die(json_encode($obj));
}

if (!User::isAdmin() || $subscription->metadata->users_id == User::getId()) {
    $obj->msg = "You Can Not do this";
    die(json_encode($obj));
}

$obj->error = false;
$obj->msg = "";
$obj->response  = $subscription->cancel();
die(json_encode($obj));
?>