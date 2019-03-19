<?php

require_once '../../../videos/configuration.php';

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isAdmin()) {
    $obj->msg = "Only for admin";
    die(json_encode($obj));
}

if (empty($_POST['agreement_id'])) {
    $obj->msg = "Empty Agreement ID";
    die(json_encode($obj));
}

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("PayPalYPT");

$agreement = PayPalYPT::getBillingAgreement($_POST['agreement_id']);

if(empty($agreement)){
    $obj->msg = "Agreement not found";
    die(json_encode($obj));
}

$obj->error = false;
$obj->msg = json_encode($agreement);
die(json_encode($obj));
?>