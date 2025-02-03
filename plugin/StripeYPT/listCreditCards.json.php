<?php
require_once '../../videos/configuration.php';
header('Content-Type: application/json');
if (!User::isLogged()) {
    gotToLoginAndComeBackHere('Please login first');
}

$subscription_id = intval($_REQUEST['subscription_id']);

if (empty($subscription_id)) {
    forbiddenPage('subscription_id plugin not found');
}

$users_id = User::getId();

$stripe = AVideoPlugin::loadPlugin("StripeYPT");
$subs = AVideoPlugin::loadPluginIfEnabled("Subscription");

if (empty($subs)) {
    forbiddenPage('Subscription plugin not found');
}

$s = new SubscriptionTable($subscription_id);

if (!User::isAdmin() && $users_id != $s->getUsers_id()) {
    forbiddenPage('This plan does not belong to you');
}

$cards = $stripe->getAllCreditCards($subscription_id);


$obj = new stdClass();
$obj->error = false;
$obj->msg = "";
$obj->cards = $cards;

echo json_encode($obj);
