<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isAdmin()) {
    $obj->msg = "Only for admin";
    die(json_encode($obj));
}

if (empty($_POST['stripe_costumer_id'])) {
    $obj->msg = "Empty Stripe Costumer ID";
    die(json_encode($obj));
}

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("StripeYPT");

$agreement = StripeYPT::getSubscriptions($_POST['stripe_costumer_id'], $_POST['plans_id']);

if(empty($agreement)){
    $obj->msg = "Agreement not found";
    die(json_encode($obj));
}
error_log("SripeAgreementStatus: ".json_encode($agreement));
$obj->error = false;
$obj->msg  = "<b>State: </b>".$agreement->status;
$obj->msg .= "<br><b>Created: </b>".date("Y-m-d H:i", $agreement->created);
$obj->msg .= "<br><b>Plan: </b>".$agreement->plan->nickname;
$obj->msg .= "<br><b>Value: </b>". YPTWallet::formatCurrency(StripeYPT::addDot($agreement->plan->amount));
$obj->msg .= "<br><b>Currency: </b>".$agreement->plan->currency;
$obj->msg .= "<br><b>Interval: </b>".$agreement->plan->interval;
$obj->msg .= "<br><b>Interval Count: </b>".$agreement->plan->interval_count;
$obj->msg .= "<br><b>Start: </b>".date("Y-m-d H:i", $agreement->start);
$obj->msg .= "<br><b>Trial end: </b>".date("Y-m-d H:i", $agreement->trial_end);
die(json_encode($obj));
?>