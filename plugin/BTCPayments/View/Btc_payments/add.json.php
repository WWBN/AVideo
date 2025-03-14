<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_payments.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('BTCPayments');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Btc_payments(@$_POST['id']);
$o->setBtc_invoices_id($_POST['btc_invoices_id']);
$o->setTransaction_identification($_POST['transaction_identification']);
$o->setAmount_received_btc($_POST['amount_received_btc']);
$o->setConfirmations($_POST['confirmations']);
$o->setCreated_php_time($_POST['created_php_time']);
$o->setModified_php_time($_POST['modified_php_time']);
$o->setJson($_POST['json']);
$o->setStore($_POST['store']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
