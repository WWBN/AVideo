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

if (empty($_REQUEST['agreement'])) {
    $obj->msg = "Empty Agreement ID";
    die(json_encode($obj));
}

if (!User::isLogged()) {
    $obj->msg = "Please login first";
    die(json_encode($obj));
}

$plugin = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");

$agreement = PayPalYPT::cancelAgreement($_REQUEST['agreement']);

if (empty($agreement)) {
    $obj->msg = "Agreement not found";
    die(json_encode($obj));
}

$obj->error = false;
_error_log("agreementCancel: ".json_encode($agreement->getAgreementDetails()));
die(json_encode($obj));
