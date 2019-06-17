<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isLogged()) {
    $obj->msg = "Only for Logged";
    die(json_encode($obj));
}

if (empty($_POST['agreement_id'])) {
    $obj->msg = "Empty Agreement ID";
    die(json_encode($obj));
}

if(!User::isAdmin() && !Subscription::isAgreementFromUser($_POST['agreement_id'], User::getId())){
    $obj->msg = "Only the owner can delete his agreement";
    die(json_encode($obj));
}

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("PayPalYPT");

$agreement = PayPalYPT::cancelAgreement($_POST['agreement_id']);

if(empty($agreement)){
    $obj->msg = "Agreement not found";
    die(json_encode($obj));
}

$subs = Subscription::getFromAgreement($_POST['agreement_id']);
$s = new SubscriptionTable($subs['id']);
$s->setAgreement_id('canceled');
$s->save();

$obj->error = false;
error_log("PayPalAgreementCancel: ".json_encode($agreement->getAgreementDetails()));
die(json_encode($obj));
?>