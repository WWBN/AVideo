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
error_log("PayPalAgreementStatus: ".json_encode($agreement->getAgreementDetails()));
$obj->error = false;
$obj->msg  = "<b>State: </b>".$agreement->getState();
$obj->msg .= "<br><b>Description: </b>".$agreement->getDescription();
//$obj->msg .= "<br><b>Plan: </b>".$agreement->getPlan()->name;
//$obj->msg .= "<br><b>Plan Frequency: </b>".$agreement->getPlan()->frequency;
//$obj->msg .= "<br><b>Plan Frequency Interval: </b>".$agreement->getPlan()->frequency_interval;
$obj->msg .= "<br><b>Last Payment: </b>".$agreement->getAgreementDetails()->last_payment_amount->value." ".$agreement->getAgreementDetails()->last_payment_amount->currency;
$obj->msg .= "<br><b>Start Date: </b>".$agreement->getStartDate();
$obj->msg .= "<br><b>Cycles Completed: </b>".$agreement->getAgreementDetails()->cycles_completed;
$obj->msg .= "<br><b>Next Billing Date: </b>".$agreement->getAgreementDetails()->next_billing_date;
$obj->msg .= "<br><b>Last Payment Date: </b>".$agreement->getAgreementDetails()->last_payment_date;
die(json_encode($obj));
?>