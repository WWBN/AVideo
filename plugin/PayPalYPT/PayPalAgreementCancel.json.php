<?php
// Preserve the provider ID before configuration.php's generic *_id integer filter.
$paypalAgreementId = $_POST['agreement_id'] ?? '';
require_once '../../videos/configuration.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isLogged()) {
    $obj->msg = "Only for Logged";
    die(json_encode($obj));
}

forbidIfNotPost();
forbidIfInvalidToken();

$plugin = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");
if (empty($plugin) || !PayPalYPT_checkout::isValidProviderId($paypalAgreementId)) {
    $obj->msg = "Empty Agreement ID";
    die(json_encode($obj));
}

if (!User::isAdmin() && !Subscription::isAgreementFromUser($paypalAgreementId, User::getId())) {
    $obj->msg = "Only the owner can delete his agreement";
    die(json_encode($obj));
}

$agreement = PayPalYPT::cancelAgreement($paypalAgreementId);

if (empty($agreement)) {
    $obj->msg = "Agreement not found";
    die(json_encode($obj));
}

$subs = Subscription::getFromAgreement($paypalAgreementId);
$s = new SubscriptionTable($subs['id']);
$s->setAgreement_id('canceled');
$s->save();

$obj->error = false;
_error_log("PayPalAgreementCancel: agreement={$paypalAgreementId}");
die(json_encode($obj));
