<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_invoices.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('BTCPayments');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Btc_invoices(@$_POST['id']);
$o->setInvoice_identification($_POST['invoice_identification']);
$o->setUsers_id($_POST['users_id']);
$o->setAmount_currency($_POST['amount_currency']);
$o->setAmount_btc($_POST['amount_btc']);
$o->setCurrency($_POST['currency']);
$o->setStatus($_POST['status']);
$o->setCreated_php_time($_POST['created_php_time']);
$o->setModified_php_time($_POST['modified_php_time']);
$o->setJson($_POST['json']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
